<?php

namespace App\Middlewares;

use App\Core\Http\AbstractMiddleware;
use App\Core\Services\AuthService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class AuthMiddleware extends AbstractMiddleware
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // Check if user is authenticated
        if (!AuthService::check()) {
            // Store intended URL for redirect after login
            $_SESSION['intended_url'] = $request->getUri()->getPath();
            
            // Redirect to login with flash message
            $_SESSION['flash'] = [
                'type' => 'warning',
                'message' => 'Please login to access this page.'
            ];
            
            return $this->createResponse(302)
                ->withHeader('Location', '/');
        }

        // Add user to request attributes for easy access in controllers
        $user = AuthService::user();
        $request = $request->withAttribute('user', $user);
        $request = $request->withAttribute('auth_id', AuthService::id());

        return $handler->handle($request);
    }
}
