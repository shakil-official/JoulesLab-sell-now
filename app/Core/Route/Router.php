<?php

namespace App\Core\Route;

use App\Core\View\View;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class Router
{
    protected array $routes = [];
    protected View $view;

    public function __construct(View $view)
    {
        $this->view = $view;
    }

    public function register(
        string $httpMethod,
        string $uri,
        string $controller,
        string $action
    ): void
    {
        $this->routes[$httpMethod][] = [
            'pattern' => $this->toRegex($uri),
            'controller' => $controller,
            'action' => $action,
        ];
    }

    protected function toRegex(string $uri): string
    {
        $pattern = preg_replace('#\{[^/]+\}#', '([^/]+)', $uri);
        return '#^' . $pattern . '$#';
    }

    /**
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws LoaderError
     */
    public function dispatch(Request $request): void
    {
        $method = $request->method();
        $uri = $request->uri();

        foreach ($this->routes[$method] ?? [] as $route) {
            if (preg_match($route['pattern'], $uri, $matches)) {

                array_shift($matches);

                $controller = new $route['controller'];

                call_user_func_array(
                    [$controller, $route['action']],
                    array_merge([$request], $matches)
                );
                return;
            }
        }

        http_response_code(404);
        $this->view->render("errors/404");
    }
}
