<?php


use App\Controllers\TestController;
use App\Core\Route\Route;
use SellNow\Controllers\AuthController;
use SellNow\Controllers\CartController;
use SellNow\Controllers\CheckoutController;
use SellNow\Controllers\DashboardController;
use SellNow\Controllers\ProductController;
use SellNow\Controllers\PublicController;
use SellNow\Middlewares\AuthMiddleware;

Route::get()
    ->url('/test')
    ->controller(TestController::class)
    ->method('index');

Route::get()
    ->url('/')
    ->controller(AuthController::class)
    ->method('loginView');

Route::post()
    ->url('/login')
    ->controller(AuthController::class)
    ->method('login');

Route::get()
    ->url('/register')
    ->controller(AuthController::class)
    ->method('registerForm');

Route::post()
    ->url('/register')
    ->controller(AuthController::class)
    ->method('register');

Route::get()
    ->url('/logout')
    ->controller(AuthController::class)
    ->method('logout');

Route::get()
    ->url('/dashboard')
    ->controller(DashboardController::class)
    ->middleware(AuthMiddleware::class)
    ->method('index');

Route::get()
    ->url('/products/add')
    ->controller(ProductController::class)
    ->middleware(AuthMiddleware::class)
    ->method('index');

Route::post()
    ->url('/products/add')
    ->controller(ProductController::class)
    ->middleware(AuthMiddleware::class)
    ->method('store');

Route::post()
    ->url('/cart/add')
    ->controller(CartController::class)
    ->middleware(AuthMiddleware::class)
    ->method('add');


Route::get()
    ->url('/cart')
    ->controller(CartController::class)
    ->middleware(AuthMiddleware::class)
    ->method('index');

Route::get()
    ->url('/cart/clear')
    ->controller(CartController::class)
    ->middleware(AuthMiddleware::class)
    ->method('clear');

Route::get()
    ->url('/products')
    ->controller(PublicController::class)
    ->middleware(AuthMiddleware::class)
    ->method('profile');

Route::get()
    ->url('/checkout')
    ->controller(CheckoutController::class)
    ->middleware(AuthMiddleware::class)
    ->method('index');

Route::post()
    ->url('/checkout/process')
    ->controller(CheckoutController::class)
    ->middleware(AuthMiddleware::class)
    ->method('process');


Route::get()
    ->url('/payment')
    ->controller(CheckoutController::class)
    ->middleware(AuthMiddleware::class)
    ->method('payment');


Route::post()
    ->url('/checkout/success')
    ->controller(CheckoutController::class)
    ->method('success');


