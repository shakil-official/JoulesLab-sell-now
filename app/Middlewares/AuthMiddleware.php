<?php

namespace App\Middlewares;

use App\Core\Http\AbstractMiddleware;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class AuthMiddleware extends AbstractMiddleware
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // Check if user is authenticated
        if (!isset($_SESSION['auth'])) {
            $factory = new \Nyholm\Psr7\Factory\Psr17Factory();
            $response = $factory->createResponse(302);
            return $response->withHeader('Location', '/login');
        }

        return $handler->handle($request);
    }
}
