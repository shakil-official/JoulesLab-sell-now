<?php

namespace App\Core\Http;

use App\Core\View\View;
use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use function FastRoute\simpleDispatcher;

class PsrRouter implements RequestHandlerInterface
{
    private array $routes = [];
    private View $view;
    private Dispatcher $dispatcher;
    private array $middlewareGroups = [];

    public function __construct(View $view)
    {
        $this->view = $view;
        $this->loadMiddlewareGroups();
    }

    public function addRoute(
        string $method,
        string $uri,
        string $controller,
        string $action,
        array $middlewares = []
    ): void {
        $this->routes[] = [
            'method' => $method,
            'uri' => $uri,
            'controller' => $controller,
            'action' => $action,
            'middlewares' => $middlewares
        ];
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $this->buildDispatcher();

        $routeInfo = $this->dispatcher->dispatch(
            $request->getMethod(),
            $request->getUri()->getPath()
        );

        return match ($routeInfo[0]) {
            Dispatcher::NOT_FOUND => $this->handleNotFound($request),
            Dispatcher::METHOD_NOT_ALLOWED => $this->handleMethodNotAllowed($request, $routeInfo[1]),
            Dispatcher::FOUND => $this->handleFound($request, $routeInfo[1], $routeInfo[2]),
        };
    }

    private function buildDispatcher(): void
    {
        if (isset($this->dispatcher)) {
            return;
        }

        $this->dispatcher = simpleDispatcher(function (RouteCollector $r) {
            foreach ($this->routes as $route) {
                $r->addRoute($route['method'], $route['uri'], [
                    'controller' => $route['controller'],
                    'action' => $route['action'],
                    'middlewares' => $route['middlewares']
                ]);
            }
        });
    }

    private function handleNotFound(ServerRequestInterface $request): ResponseInterface
    {
        $factory = new \Nyholm\Psr7\Factory\Psr17Factory();
        $response = $factory->createResponse(404);

        if (class_exists('App\\Core\\Controller\\ErrorController')) {
            $handler = new ControllerRequestHandler(
                'App\\Core\\Controller\\ErrorController',
                'notFound',
                $this->view
            );
            return $handler->handle($request);
        }

        $response->getBody()->write('<h1>404 - Not Found</h1>');
        return $response;
    }

    private function handleMethodNotAllowed(ServerRequestInterface $request, array $allowedMethods): ResponseInterface
    {
        $factory = new \Nyholm\Psr7\Factory\Psr17Factory();
        $response = $factory->createResponse(405);
        $response = $response->withHeader('Allow', implode(', ', $allowedMethods));

        if (class_exists('App\\Core\\Controller\\ErrorController')) {
            $handler = new ControllerRequestHandler(
                'App\\Core\\Controller\\ErrorController',
                'methodNotAllowed',
                $this->view
            );
            return $handler->handle($request);
        }

        $response->getBody()->write('405 Method Not Allowed');
        return $response;
    }

    /**
     * @throws \Exception
     */
    private function handleFound(ServerRequestInterface $request, array $handler, array $vars): ResponseInterface
    {
        // Add route params as attributes
        foreach ($vars as $key => $value) {
            $request = $request->withAttribute($key, $value);
        }

        // Create controller handler
        $controllerHandler = new ControllerRequestHandler(
            $handler['controller'],
            $handler['action'],
            $this->view
        );

        // Apply middleware
        $middlewares = $handler['middlewares'] ?? [];
        $middlewareHandler = new MiddlewareHandler($middlewares, $controllerHandler);

        return $middlewareHandler->handle($request);
    }

    private function loadMiddlewareGroups(): void
    {
        $this->middlewareGroups = [
            'web' => [],
            'api' => [],
            'auth' => [],
            'guest' => [],
        ];
    }
}
