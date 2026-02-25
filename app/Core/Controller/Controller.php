<?php

namespace App\Core\Controller;

use App\Core\Container\Container;
use App\Core\Route\Request;
use App\Core\View\View;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

abstract class Controller
{
    protected View $view;
    protected Container $container;

    public function __construct(View $view, Container $container)
    {
        $this->view = $view;
        $this->container = $container;
    }

    protected function getRequest(ServerRequestInterface $serverRequest): Request
    {
        // Try to get the custom request from attributes first
        $customRequest = $serverRequest->getAttribute('custom_request');
        if ($customRequest instanceof Request) {
            return $customRequest;
        }

        // If no custom request is available, create a new one
        return new Request();
    }

    protected function getInput(ServerRequestInterface $serverRequest, string $key, $default = null)
    {
        // Try to get from custom request first
        $customRequest = $this->getRequest($serverRequest);
        $value = $customRequest->input($key, $default);
        
        if ($value !== $default) {
            return $value;
        }

        // Fall back to PSR-7 request
        $data = $serverRequest->getParsedBody();
        return is_array($data) ? ($data[$key] ?? $default) : $default;
    }

    protected function render(string $template, array $data = []): ResponseInterface
    {
        $flashData = [
            'success' => \App\Core\Config\Helper::getMessage('success'),
            'error'   => \App\Core\Config\Helper::getMessage('error'),
        ];

        $data = array_merge($data, $flashData);

        // Get rendered content from view
        $content = $this->view->render($template, $data);

        // Clear messages after passing
        unset($_SESSION['_message']['success'], $_SESSION['_message']['error']);

        // Create PSR-7 response with content
        $factory = new \Nyholm\Psr7\Factory\Psr17Factory();
        $response = $factory->createResponse(200);
        $response->getBody()->write($content);
        return $response;
    }

    protected function json(array $data, int $status = 200): ResponseInterface
    {
        $factory = new \Nyholm\Psr7\Factory\Psr17Factory();
        $response = $factory->createResponse($status);
        $response->getBody()->write(json_encode($data));
        return $response->withHeader('Content-Type', 'application/json');
    }

    protected function redirect(string $uri, int $status = 302): ResponseInterface
    {
        $factory = new \Nyholm\Psr7\Factory\Psr17Factory();
        return $factory->createResponse($status)->withHeader('Location', $uri);
    }
}
