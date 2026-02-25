<?php

namespace App\Core\Http;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use App\Core\View\View;
use App\Core\Container\Container;

class ControllerRequestHandler implements RequestHandlerInterface
{
    private string $controller;
    private string $method;
    private View $view;
    private Container $container;

    public function __construct(string $controller, string $method, View $view, Container $container)
    {
        $this->controller = $controller;
        $this->method = $method;
        $this->view = $view;
        $this->container = $container;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        if (!class_exists($this->controller)) {
            return $this->createErrorResponse(
                500,
                "Controller not found: {$this->controller}"
            );
        }

        // Get controller from container to ensure proper DI
        $controller = $this->container->get($this->controller);

        if (!method_exists($controller, $this->method)) {
            return $this->createErrorResponse(
                500,
                "Method {$this->method} not found in {$this->controller}"
            );
        }

        try {
            $response = $controller->{$this->method}($request);
            
            if ($response instanceof ResponseInterface) {
                return $response;
            }

            // If controller returns void, create empty response
            $factory = new \Nyholm\Psr7\Factory\Psr17Factory();
            return $factory->createResponse(200);
        } catch (\Exception $e) {
            return $this->createErrorResponse(
                500,
                "Internal Server Error: " . $e->getMessage()
            );
        }
    }

    private function createErrorResponse(int $code, string $message): ResponseInterface
    {
        $factory = new \Nyholm\Psr7\Factory\Psr17Factory();
        $response = $factory->createResponse($code);
        $response->getBody()->write($message);
        return $response->withHeader('Content-Type', 'text/plain');
    }
}
