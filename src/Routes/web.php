<?php


use App\Controllers\TestController;
use App\Core\Route\Route;

Route::get()
    ->url('/test')
    ->controller(TestController::class)
    ->method('index');
