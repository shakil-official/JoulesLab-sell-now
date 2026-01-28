<?php


use App\Controllers\TestController;
use App\Core\Route\Route;

Route::get()
    ->url('/test')
    ->controller(TestController::class)
    ->method('index');

Route::get()
    ->url('/')
    ->controller(\SellNow\Controllers\AuthController::class)
    ->method('loginView');

Route::post()
    ->url('/login')
    ->controller(\SellNow\Controllers\AuthController::class)
    ->method('login');

Route::get()
    ->url('/register')
    ->controller(\SellNow\Controllers\AuthController::class)
    ->method('registerForm');

Route::post()
    ->url('/register')
    ->controller(\SellNow\Controllers\AuthController::class)
    ->method('register');

Route::get()
    ->url('/dashboard')
    ->controller(\SellNow\Controllers\AuthController::class)
    ->method('dashboard');