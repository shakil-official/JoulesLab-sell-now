<?php

namespace SellNow\Middlewares;

use App\Core\Route\Request;
use App\Core\Config\Helper;
use App\Core\Services\AuthService;

class AuthMiddleware
{
    public function handle(Request $request): void
    {
        if (empty(AuthService::userId())) {
            Helper::redirect('/', ['error' => 'Please log in first']);
        }
    }
}
