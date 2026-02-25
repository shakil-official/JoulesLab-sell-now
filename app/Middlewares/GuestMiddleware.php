<?php

namespace App\Middlewares;

use App\Core\Http\AbstractMiddleware;
use App\Core\Services\AuthService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class GuestMiddleware extends AbstractMiddleware
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // Check if user is already authenticated
        if (AuthService::check()) {
            // Redirect authenticated users away from guest pages
            return $this->createResponse(302)
                ->withHeader('Location', '/dashboard');
        }

        // User is guest, continue to requested page
        return $handler->handle($request);
    }
}
