<?php

namespace App\Core\Http;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

abstract class AbstractRequestHandler implements RequestHandlerInterface
{
    protected function createResponse(int $code = 200, string $reasonPhrase = ''): ResponseInterface
    {
        $factory = new \Nyholm\Psr7\Factory\Psr17Factory();
        return $factory->createResponse($code, $reasonPhrase);
    }

    protected function jsonResponse(array $data, int $status = 200): ResponseInterface
    {
        $response = $this->createResponse($status);
        $response->getBody()->write(json_encode($data));
        return $response->withHeader('Content-Type', 'application/json');
    }

    protected function redirectResponse(string $uri, int $status = 302): ResponseInterface
    {
        return $this->createResponse($status)->withHeader('Location', $uri);
    }
}
