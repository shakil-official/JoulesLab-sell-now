<?php

namespace App\Core\Route;

class RouteDefinition
{
    protected string $httpMethod;
    protected Router $router;
    protected array $definition = [];

    public function __construct(string $httpMethod, Router $router)
    {
        $this->httpMethod = $httpMethod;
        $this->router     = $router;
    }

    public function url(string $uri): self
    {
        $this->definition['uri'] = $uri;
        preg_match_all('#\{([^}]+)\}#', $uri, $matches);
        $this->definition['params'] = $matches[1] ?? [];

        return $this;
    }

    public function controller(string $controller): self
    {
        if (!class_exists($controller)) {
            throw new \InvalidArgumentException("Controller not found: {$controller}");
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
            $this->definition['params'] ?? []
        );
    }
}
