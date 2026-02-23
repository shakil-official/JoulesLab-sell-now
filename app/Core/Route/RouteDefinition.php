<?php

namespace App\Core\Route;

class RouteDefinition
{
    protected string $httpMethod;
    protected Router $router;
    protected array $definition = [];
    protected ?string $name = null;
    protected array $where = [];

    public function __construct(string $httpMethod, Router $router)
    {
        $this->httpMethod = $httpMethod;
        $this->router     = $router;
        $this->definition['middlewares'] = [];
    }

    public function url(string $uri): self
    {
        $prefix = Route::getCurrentPrefix();
        $uri = $prefix . $uri;
        
        $this->definition['uri'] = $uri;
        preg_match_all('#\{([^}]+)\}#', $uri, $matches);
        $this->definition['params'] = $matches[1] ?? [];

        return $this;
    }

    public function controller(string $controller): self
    {
        if (!class_exists($controller)) {
            // Don't throw error during route definition, let router handle it
            // This allows routes to be defined even if controller doesn't exist yet
        }

        $this->definition['controller'] = $controller;
        return $this;
    }

    public function method(string $method): void
    {
        $this->router->register(
            $this->httpMethod,
            $this->definition['uri'],
            $this->definition['controller'],
            $method,
            $this->definition['params'] ?? [],
            $this->definition['middlewares'] ?? [],
            $this->name,
            $this->where,
            $this->definition['redirect'] ?? null,
            $this->definition['view'] ?? null
        );
    }

    public function redirect(string $destination, int $status = 302): self
    {
        $this->definition['redirect'] = [$destination, $status];
        return $this;
    }

    public function view(string $view, array $data = []): self
    {
        $this->definition['view'] = [$view, $data];
        return $this;
    }

    public function middleware(string|array $middleware): self
    {
        if (is_array($middleware)) {
            $this->definition['middlewares'] = array_merge(
                $this->definition['middlewares'],
                $middleware
            );
        } else {
            $this->definition['middlewares'][] = $middleware;
        }

        return $this;
    }

    public function name(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function where(string $param, string $pattern): self
    {
        $this->where[$param] = $pattern;
        return $this;
    }

    public function whereArray(array $constraints): self
    {
        $this->where = array_merge($this->where, $constraints);
        return $this;
    }

}
