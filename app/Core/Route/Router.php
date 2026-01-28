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
        string $action,
        array $params = [],

    ): void
    {
        $this->routes[$httpMethod][] = [
            'pattern' => $this->toRegex($uri),
            'controller' => $controller,
            'action' => $action,
            'params'     => $params,
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

                array_shift($matches); // remove full match

                $paramNames = $route['params'] ?? [];
                $params = [];
                foreach ($paramNames as $i => $name) {
                    $params[$name] = $matches[$i] ?? null;
                }

                $request->setRouteParams($params);

                $controller = new $route['controller']($this->view);

                call_user_func_array([$controller, $route['action']], [$request]);

                return;
            }
        }

        http_response_code(404);
        $this->view->render("errors/404");
    }

}
