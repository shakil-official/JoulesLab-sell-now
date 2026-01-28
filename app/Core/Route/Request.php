<?php

namespace App\Core\Route;

class Request
{
    protected array $routeParams = [];
    public function uri(): string
    {
        return '/' . trim(
                parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH),
                '/'
            );
    }

    public function method(): string
    {
        return $_SERVER['REQUEST_METHOD'];
    }

    public function setRouteParams(array $params): void
    {
        $this->routeParams = $params;
    }

    public function input(string $key, $default = null)
    {
        return $this->routeParams[$key] ?? $_REQUEST[$key] ?? $default;
    }
}
