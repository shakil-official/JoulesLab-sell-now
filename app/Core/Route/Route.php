<?php

namespace App\Core\Route;

class Route
{
    protected static Router $router;
    protected static array $prefixStack = [];
    protected static array $middlewareStack = [];
    protected static array $namedRoutes = [];

    public static function init(Router $router): void
    {
        self::$router = $router;
    }

    public static function get(): RouteDefinition
    {
        self::checkRouter();
        return new RouteDefinition('GET', self::$router);
    }

    public static function post(): RouteDefinition
    {
        self::checkRouter();
        return new RouteDefinition('POST', self::$router);
    }

    public static function put(): RouteDefinition
    {
        self::checkRouter();
        return new RouteDefinition('PUT', self::$router);
    }

    public static function patch(): RouteDefinition
    {
        self::checkRouter();
        return new RouteDefinition('PATCH', self::$router);
    }

    public static function delete(): RouteDefinition
    {
        self::checkRouter();
        return new RouteDefinition('DELETE', self::$router);
    }

    public static function options(): RouteDefinition
    {
        self::checkRouter();
        return new RouteDefinition('OPTIONS', self::$router);
    }

    public static function any(): RouteDefinition
    {
        self::checkRouter();
        return new RouteDefinition('GET,POST,PUT,PATCH,DELETE,OPTIONS,HEAD', self::$router);
    }

    public static function match(array $methods): RouteDefinition
    {
        self::checkRouter();
        return new RouteDefinition(implode(',', array_map('strtoupper', $methods)), self::$router);
    }

    private static function checkRouter(): void
    {
        if (!isset(self::$router)) {
            throw new \RuntimeException('Router not initialized. Call Route::init() first.');
        }
    }

    public static function prefix(string $prefix, callable $callback): void
    {
        self::$prefixStack[] = $prefix;

        $callback();
        
        array_pop(self::$prefixStack);
    }

    public static function getCurrentPrefix(): string
    {
        return implode('', self::$prefixStack);
    }

    public static function middleware(string|array $middleware, callable $callback): void
    {
        array_push(self::$middlewareStack, $middleware);
        
        $callback();
        
        array_pop(self::$middlewareStack);
    }

    public static function getCurrentMiddleware(): array
    {
        $middlewares = [];
        foreach (self::$middlewareStack as $middleware) {
            if (is_array($middleware)) {
                $middlewares = array_merge($middlewares, $middleware);
            } else {
                $middlewares[] = $middleware;
            }
        }
        return $middlewares;
    }

    public static function group(array $attributes, callable $callback): void
    {
        $prefixStack = [];
        $middlewareStack = [];
        
        if (isset($attributes['prefix'])) {
            array_push(self::$prefixStack, $attributes['prefix']);
            $prefixStack[] = $attributes['prefix'];
        }
        
        if (isset($attributes['middleware'])) {
            array_push(self::$middlewareStack, $attributes['middleware']);
            $middlewareStack[] = $attributes['middleware'];
        }
        
        $callback();
        
        foreach ($prefixStack as $prefix) {
            array_pop(self::$prefixStack);
        }
        
        foreach ($middlewareStack as $middleware) {
            array_pop(self::$middlewareStack);
        }
    }

    public static function resource(string $name, string $controller): void
    {
        $routes = [
            'index' => ['GET', '/' . $name, 'index'],
            'create' => ['GET', '/' . $name . '/create', 'create'],
            'store' => ['POST', '/' . $name, 'store'],
            'show' => ['GET', '/' . $name . '/{id}', 'show'],
            'edit' => ['GET', '/' . $name . '/{id}/edit', 'edit'],
            'update' => ['PUT', '/' . $name . '/{id}', 'update'],
            'destroy' => ['DELETE', '/' . $name . '/{id}', 'destroy'],
        ];

        foreach ($routes as $routeName => [$method, $uri, $action]) {
            self::{$method}()
                ->url($uri)
                ->controller($controller)
                ->name($name . '.' . $routeName)
                ->method($action);
        }
    }

    public static function apiResource(string $name, string $controller): void
    {
        $routes = [
            'index' => ['GET', '/' . $name, 'index'],
            'store' => ['POST', '/' . $name, 'store'],
            'show' => ['GET', '/' . $name . '/{id}', 'show'],
            'update' => ['PUT', '/' . $name . '/{id}', 'update'],
            'destroy' => ['DELETE', '/' . $name . '/{id}', 'destroy'],
        ];

        foreach ($routes as $routeName => [$method, $uri, $action]) {
            self::{$method}()
                ->url($uri)
                ->controller($controller)
                ->name($name . '.' . $routeName)
                ->method($action);
        }
    }

    public static function redirect(string $from, string $to, int $status = 302): void
    {
        self::get()
            ->url($from)
            ->controller(RedirectController::class)
            ->redirect($to, $status)
            ->method('redirect');
    }

    public static function view(string $uri, string $view, array $data = []): void
    {
        self::get()
            ->url($uri)
            ->controller(ViewController::class)
            ->view($view, $data)
            ->method('render');
    }

    public static function getNamedRoutes(): array
    {
        return self::$namedRoutes;
    }

    public static function addNamedRoute(string $name, string $uri, array $params = []): void
    {
        self::$namedRoutes[$name] = ['uri' => $uri, 'params' => $params];
    }

    public static function route(string $name, array $parameters = []): string
    {
        if (!isset(self::$namedRoutes[$name])) {
            throw new \InvalidArgumentException("Route '{$name}' not found.");
        }

        $route = self::$namedRoutes[$name];
        $uri = $route['uri'];

        foreach ($parameters as $key => $value) {
            $uri = str_replace('{' . $key . '}', $value, $uri);
        }

        return $uri;
    }
}
