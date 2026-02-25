<?php

namespace App\Core\Route;

use App\Core\Container\Container;
use App\Core\Http\ControllerRequestHandler;
use App\Core\Http\MiddlewareHandler;
use App\Core\Http\Request;
use App\Core\Services\CsrfService;
use App\Core\View\View;
use Exception;
use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use function FastRoute\simpleDispatcher;

class Router
{
    protected array $routes = [];
    protected View $view;
    protected Dispatcher $dispatcher;
    protected array $middlewareGroups = [];
    protected array $routeCache = [];
    protected bool $cacheEnabled = false;
    protected string $cacheFile;
    protected CsrfService $csrfService;
    protected Container $container;

    public function __construct(View $view, CsrfService $csrfService, Container $container, ?string $cacheFile = null)
    {
        $this->view = $view;
        $this->csrfService = $csrfService;
        $this->container = $container;
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

        // Store route definition
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
                        'view' => $view,
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
                \App\Middlewares\AuthMiddleware::class,
            ],
            'guest' => [
                \App\Middlewares\GuestMiddleware::class,
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
            'timestamp' => time(),
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
    public function dispatch(ServerRequestInterface $request): ResponseInterface
    {
        $this->buildDispatcher();

        $routeInfo = $this->dispatcher->dispatch(
            $request->getMethod(),
            $request->getUri()->getPath()
        );

        switch ($routeInfo[0]) {
            case Dispatcher::NOT_FOUND:
                return $this->handleNotFound($request);

            case Dispatcher::METHOD_NOT_ALLOWED:
                return $this->handleMethodNotAllowed($request, $routeInfo[1]);

            case Dispatcher::FOUND:
                return $this->handleFound($request, $routeInfo[1], $routeInfo[2]);
        }
    }

    protected function handleNotFound(ServerRequestInterface $request): ResponseInterface
    {
        $factory = new Psr17Factory();
        $response = $factory->createResponse(404);

        // Try to handle with custom 404 controller if exists
        if (class_exists('App\\Core\\Controller\\ErrorController')) {
            $handler = new ControllerRequestHandler(
                'App\\Core\\Controller\\ErrorController',
                'notFound',
                $this->view,
                $this->container
            );
            return $handler->handle($request);
        } else {
            try {
                $content = $this->view->render('errors/404');
                $response->getBody()->write($content);
            } catch (Exception $e) {
                $response->getBody()->write('<h1>404 - Not Found</h1>');
            }
        }

        return $response;
    }

    protected function handleMethodNotAllowed(ServerRequestInterface $request, array $allowedMethods): ResponseInterface
    {
        $factory = new Psr17Factory();
        $response = $factory->createResponse(405);
        $response = $response->withHeader('Allow', implode(', ', $allowedMethods));

        // Try to handle with custom 405 controller if exists
        if (class_exists('App\\Core\\Controller\\ErrorController')) {
            // Add allowed methods to request attributes
            $request = $request->withAttribute('allowedMethods', $allowedMethods);
            
            $handler = new ControllerRequestHandler(
                'App\\Core\\Controller\\ErrorController',
                'methodNotAllowed',
                $this->view,
                $this->container
            );
            return $handler->handle($request);
        } else {
            $response->getBody()->write('405 Method Not Allowed. Allowed methods: ' . implode(', ', $allowedMethods));
        }

        return $response;
    }

    /**
     * @throws Exception
     */
    protected function handleFound(ServerRequestInterface $request, array $handler, array $vars): ResponseInterface
    {
        // Inject route params into Request
        if ($request instanceof Request) {
            $request->setRouteParams($vars);
        } else {
            // For PSR-7 requests, add as attribute
            $request = $request->withAttribute('route_params', $vars);
        }

        // CSRF validation for POST
        if ($request->getMethod() === 'POST') {
            $data = $request->getParsedBody();

            $csrfToken = is_array($data) ? ($data['csrf'] ?? null) : null;
            
            if (!$this->csrfService->validate($csrfToken)) {
                $factory = new Psr17Factory();
                $response = $factory->createResponse(403);
                $response->getBody()->write('<h1>403 - Forbidden</h1><p>Invalid or missing CSRF token.</p>');
                return $response;
            }
            $this->csrfService->forget();
        }

        // Handle special route types
        if (isset($handler['redirect'])) {
            [$destination, $status] = $handler['redirect'];
            $factory = new Psr17Factory();
            return $factory->createResponse($status)->withHeader('Location', $destination);
        }

        if (isset($handler['view'])) {
            [$view, $data] = $handler['view'];
            $factory = new Psr17Factory();
            $response = $factory->createResponse(200);
            
            try {
                $content = $this->view->render($view, $data);
                $response->getBody()->write($content);
            } catch (Exception $e) {
                $response = $factory->createResponse(500);
                $response->getBody()->write('View template not found: ' . htmlspecialchars($view));
            }
            
            return $response;
        }

        // Create controller handler
        $controllerHandler = new ControllerRequestHandler(
            $handler['controller'],
            $handler['action'],
            $this->view,
            $this->container
        );

        // Apply middleware
        $middlewares = $handler['middlewares'] ?? [];
        $middlewareHandler = new MiddlewareHandler($middlewares, $controllerHandler);

        return $middlewareHandler->handle($request);
    }
}