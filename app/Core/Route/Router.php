<?php

namespace App\Core\Route;

use FastRoute\RouteCollector;
use FastRoute\Dispatcher;
use function FastRoute\simpleDispatcher;
use App\Core\Config\Csrf;
use App\Core\View\View;

class Router
{
    protected array $routes = [];
    protected View $view;
    protected Dispatcher $dispatcher;
    protected array $middlewareGroups = [];
    protected array $routeCache = [];
    protected bool $cacheEnabled = false;
    protected string $cacheFile;

    public function __construct(View $view, string $cacheFile = null)
    {
        $this->view = $view;
        $this->cacheFile = $cacheFile ?? sys_get_temp_dir() . '/routes.cache';
        $this->loadMiddlewareGroups();
    }

    public function register(
        string $httpMethod,
        string $uri,
        string $controller,
        string $action,
        array $params = [],
        array $middlewares = [],
        ?string $name = null,
        array $where = [],
        ?array $redirect = null,
        ?array $view = null
    ): void {
        // Apply parameter constraints
        if (!empty($where)) {
            foreach ($where as $param => $pattern) {
                $uri = str_replace('{' . $param . '}', '{' . $param . ':' . $pattern . '}', $uri);
            }
        }

        // Merge global middleware from Route class
        $globalMiddlewares = Route::getCurrentMiddleware();
        $middlewares = array_merge($globalMiddlewares, $middlewares);

        // Resolve middleware groups
        $resolvedMiddlewares = [];
        foreach ($middlewares as $middleware) {
            if (isset($this->middlewareGroups[$middleware])) {
                $resolvedMiddlewares = array_merge($resolvedMiddlewares, $this->middlewareGroups[$middleware]);
            } else {
                $resolvedMiddlewares[] = $middleware;
            }
        }

        // store route definition
        // Handle comma-separated methods
        $methods = explode(',', $httpMethod);
        foreach ($methods as $method) {
            $method = trim($method);
            if (!empty($method)) {
                $route = [
                    $method,
                    $uri,
                    [
                        'controller' => $controller,
                        'action' => $action,
                        'middlewares' => $resolvedMiddlewares,
                        'name' => $name,
                        'params' => $params,
                        'redirect' => $redirect,
                        'view' => $view
                    ]
                ];
                $this->routes[] = $route;
            }
        }

        // Add to named routes if name is provided
        if ($name) {
            Route::addNamedRoute($name, $uri, $params);
        }
    }

    protected function loadMiddlewareGroups(): void
    {
        $this->middlewareGroups = [
            'web' => [
                // Add web middleware classes here
            ],
            'api' => [
                // Add API middleware classes here
            ],
            'auth' => [
                // Add authentication middleware
            ],
            'guest' => [
                // Add guest middleware
            ],
        ];
    }

    public function addMiddlewareGroup(string $name, array $middlewares): void
    {
        $this->middlewareGroups[$name] = $middlewares;
    }

    protected function buildDispatcher(): void
    {
        if (isset($this->dispatcher)) {
            return;
        }

        // Check cache first
        if ($this->cacheEnabled && $this->loadFromCache()) {
            return;
        }

        $this->dispatcher = simpleDispatcher(function (RouteCollector $r) {
            foreach ($this->routes as $route) {
                [$method, $uri, $handler] = $route;
                
                // Handle comma-separated methods
                $methods = explode(',', $method);
                foreach ($methods as $m) {
                    $m = trim($m);
                    if (!empty($m)) {
                        $r->addRoute($m, $uri, $handler);
                    }
                }
            }
        });

        // Save to cache
        if ($this->cacheEnabled) {
            $this->saveToCache();
        }
    }

    protected function loadFromCache(): bool
    {
        if (!file_exists($this->cacheFile)) {
            return false;
        }

        $cachedData = file_get_contents($this->cacheFile);
        if ($cachedData === false) {
            return false;
        }

        $data = unserialize($cachedData);
        if ($data === false || !isset($data['dispatcher'])) {
            return false;
        }

        $this->dispatcher = $data['dispatcher'];
        return true;
    }

    protected function saveToCache(): void
    {
        $data = [
            'dispatcher' => $this->dispatcher,
            'routes' => $this->routes,
            'timestamp' => time()
        ];

        file_put_contents($this->cacheFile, serialize($data));
    }

    public function enableCache(): void
    {
        $this->cacheEnabled = true;
    }

    public function disableCache(): void
    {
        $this->cacheEnabled = false;
        if (file_exists($this->cacheFile)) {
            unlink($this->cacheFile);
        }
    }

    public function clearCache(): void
    {
        if (file_exists($this->cacheFile)) {
            unlink($this->cacheFile);
        }
        unset($this->dispatcher);
    }

    public function getRoutes(): array
    {
        return $this->routes;
    }

    public function getRouteByName(string $name): ?array
    {
        foreach ($this->routes as $route) {
            $handler = $route[2];
            if (isset($handler['name']) && $handler['name'] === $name) {
                return $route;
            }
        }
        return null;
    }

    /**
     * @param Request $request
     */
    public function dispatch(Request $request): void
    {
        $this->buildDispatcher();

        $routeInfo = $this->dispatcher->dispatch(
            $request->method(),
            $request->uri()
        );

        switch ($routeInfo[0]) {
            case Dispatcher::NOT_FOUND:
                $this->handleNotFound($request);
                return;

            case Dispatcher::METHOD_NOT_ALLOWED:
                $this->handleMethodNotAllowed($request, $routeInfo[1]);
                return;

            case Dispatcher::FOUND:
                $this->handleFound($request, $routeInfo[1], $routeInfo[2]);
                return;
        }
    }

    protected function handleNotFound(Request $request): void
    {
        http_response_code(404);
        
        // Try to handle with custom 404 controller if exists
        if (class_exists('App\\Controllers\\ErrorController')) {
            $controller = new \App\Controllers\ErrorController($this->view);
            $controller->notFound($request);
        } else {
            $this->view->render("errors/404");
        }
    }

    protected function handleMethodNotAllowed(Request $request, array $allowedMethods): void
    {
        http_response_code(405);
        header('Allow: ' . implode(', ', $allowedMethods));
        
        // Try to handle with custom 405 controller if exists
        if (class_exists('App\\Controllers\\ErrorController')) {
            $controller = new \App\Controllers\ErrorController($this->view);
            $controller->methodNotAllowed($request, $allowedMethods);
        } else {
            echo "405 Method Not Allowed. Allowed methods: " . implode(', ', $allowedMethods);
        }
    }

    protected function handleFound(Request $request, array $handler, array $vars): void
    {
        // inject route params into Request
        $request->setRouteParams($vars);

        // CSRF validation for POST
        if ($request->method() === 'POST') {
            if (!Csrf::validate($request->input('csrf'))) {
                http_response_code(403);
                try {
                    $this->view->render('errors/403');
                } catch (\Twig\Error\LoaderError $e) {
                    echo "<h1>403 - Forbidden</h1>";
                    echo "<p>Invalid or missing CSRF token.</p>";
                }
                return;
            }
            Csrf::forget();
        }

        // run middleware
        foreach ($handler['middlewares'] ?? [] as $middlewareClass) {
            if (!class_exists($middlewareClass)) {
                http_response_code(500);
                echo "<h1>500 - Internal Server Error</h1>";
                echo "<p>Middleware class not found: " . htmlspecialchars($middlewareClass) . "</p>";
                return;
            }

            $middleware = new $middlewareClass();

            if (!method_exists($middleware, 'handle')) {
                http_response_code(500);
                echo "<h1>500 - Internal Server Error</h1>";
                echo "<p>Middleware " . htmlspecialchars($middlewareClass) . " must have a handle() method</p>";
                return;
            }

            $result = $middleware->handle($request);
            
            // If middleware returns a response, stop processing
            if ($result !== null) {
                return;
            }
        }

        // Handle special route types
        if (isset($handler['redirect'])) {
            [$destination, $status] = $handler['redirect'];
            header("Location: {$destination}", true, $status);
            return;
        }

        if (isset($handler['view'])) {
            [$view, $data] = $handler['view'];
            try {
                $this->view->render($view, $data);
            } catch (\Twig\Error\LoaderError $e) {
                http_response_code(500);
                echo "<h1>500 - Internal Server Error</h1>";
                echo "<p>View template not found: " . htmlspecialchars($view) . "</p>";
            }
            return;
        }

        // create controller
        if (!class_exists($handler['controller'])) {
            http_response_code(500);
            echo "<h1>500 - Internal Server Error</h1>";
            echo "<p>Controller not found: " . htmlspecialchars($handler['controller']) . "</p>";
            return;
        }

        $controller = new $handler['controller']($this->view);

        // call action
        if (!method_exists($controller, $handler['action'])) {
            http_response_code(500);
            echo "<h1>500 - Internal Server Error</h1>";
            echo "<p>Method " . htmlspecialchars($handler['action']) . " not found in " . htmlspecialchars($handler['controller']) . "</p>";
            return;
        }

        call_user_func([$controller, $handler['action']], $request);
    }
}