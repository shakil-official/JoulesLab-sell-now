<?php

namespace SellNow\Middlewares;

use App\Core\Config\Helper;
use App\Core\Services\AuthService;
use Psr\Http\Message\ServerRequestInterface;

class AuthMiddleware
{
    public function handle(ServerRequestInterface $request): void
    {
        if (empty(AuthService::userId())) {
            Helper::redirect('/', ['error' => 'Please log in first']);
        }
    }
}
