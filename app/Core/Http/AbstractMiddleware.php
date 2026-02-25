<?php

namespace App\Core\Http;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;

abstract class AbstractMiddleware implements MiddlewareInterface
{
    protected function createResponse(int $code = 200, string $reasonPhrase = ''): ResponseInterface
    {
        $factory = new \Nyholm\Psr7\Factory\Psr17Factory();
        return $factory->createResponse($code, $reasonPhrase);
    }
}
