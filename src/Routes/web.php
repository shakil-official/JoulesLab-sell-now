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