<?php

namespace App\Core\Route;

class Route
{
    protected static Router $router;

    public static function init(Router $router): void
    {
        self::$router = $router;
    }

    public static function get(): RouteDefinition
    {
        return new RouteDefinition('GET', self::$router);
    }

    public static function post(): RouteDefinition
    {
        return new RouteDefinition('POST', self::$router);
    }
}
