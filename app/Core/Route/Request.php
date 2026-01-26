<?php

namespace App\Core\Route;

class Request
{
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

    public function input(string $key, $default = null)
    {
        return $_REQUEST[$key] ?? $default;
    }
}
