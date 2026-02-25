<?php

namespace App\Middlewares;

use App\Core\Http\AbstractMiddleware;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class CsrfMiddleware extends AbstractMiddleware
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if ($request->getMethod() === 'POST') {
            $data = $request->getParsedBody();
            $csrfToken = $data['csrf'] ?? null;

            if (!\App\Core\Config\Csrf::validate($csrfToken)) {
                $factory = new \Nyholm\Psr7\Factory\Psr17Factory();
                $response = $factory->createResponse(403);
                $response->getBody()->write('Invalid CSRF token');
                return $response;
            }

            \App\Core\Config\Csrf::forget();
        }

        return $handler->handle($request);
    }
}
