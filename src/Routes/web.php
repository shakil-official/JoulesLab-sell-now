<?php

use App\Controllers\TestController;
use App\Core\Route\Route;
use SellNow\Controllers\AuthController;
use SellNow\Controllers\CartController;
use SellNow\Controllers\CheckoutController;
use SellNow\Controllers\DashboardController;
use SellNow\Controllers\ProductController;
use SellNow\Controllers\PublicController;

// Authentication Routes (Guest)
Route::group(['middleware' => 'guest'], function () {
    Route::get()->url('/')->controller(AuthController::class)->name('login')->method('loginView');
    Route::post()->url('/login')->controller(AuthController::class)->name('login.post')->method('login');
    Route::get()->url('/register')->controller(AuthController::class)->name('register')->method('registerForm');
    Route::post()->url('/register')->controller(AuthController::class)->name('register.post')->method('register');
});

// Logout Route
Route::get()->url('/logout')->controller(AuthController::class)->name('logout')->method('logout');

// Authenticated Routes
Route::group(['middleware' => 'auth'], function () {

    // Dashboard
    Route::get()->url('/dashboard')->controller(DashboardController::class)->name('dashboard')->method('index');

    // Products Resource Routes
    Route::prefix('/products', function () {
        Route::get()->url('/add')->controller(ProductController::class)->name('products.create')->method('index');
        Route::post()->url('/add')->controller(ProductController::class)->name('products.store')->method('store');

        // Product with parameter constraints
        Route::get()->url('/{id}')->controller(ProductController::class)
            ->name('products.show')
            ->where('id', '[0-9]+')
            ->method('show');

        Route::put()->url('/{id}')->controller(ProductController::class)
            ->name('products.update')
            ->where('id', '[0-9]+')
            ->method('update');

        Route::delete()->url('/{id}')->controller(ProductController::class)
            ->name('products.delete')
            ->where('id', '[0-9]+')
            ->method('delete');
    });

    // Public Products List
    Route::get()->url('/products')->controller(PublicController::class)->name('products.list')->method('profile');

    // Cart Routes
    Route::prefix('/cart', function () {
        Route::get()->url('')->controller(CartController::class)->name('cart.index')->method('index');
        Route::get()->url('/')->controller(CartController::class)->name('cart.index.trailing')->method('index');
        Route::post()->url('/add')->controller(CartController::class)->name('cart.add')->method('add');
        Route::get()->url('/clear')->controller(CartController::class)->name('cart.clear')->method('clear');

        // Cart item operations
        Route::put()->url('/item/{id}')->controller(CartController::class)
            ->name('cart.update')
            ->where('id', '[0-9]+')
            ->method('updateItem');

        Route::delete()->url('/item/{id}')->controller(CartController::class)
            ->name('cart.remove')
            ->where('id', '[0-9]+')
            ->method('removeItem');
    });

    // Checkout Routes
    Route::prefix('/checkout', function () {
        Route::get()->url('')->controller(CheckoutController::class)->name('checkout.index')->method('index');
        Route::post()->url('/process')->controller(CheckoutController::class)->name('checkout.process')->method('process');
        Route::post()->url('/success')->controller(CheckoutController::class)->name('checkout.success')->method('success');
    });

    // Payment Routes
    Route::prefix('/payment', function () {
        Route::get()->url('')->controller(CheckoutController::class)->name('payment.index')->method('payment');
        Route::post()->url('/process')->controller(CheckoutController::class)->name('payment.process')->method('processPayment');
        Route::get()->url('/callback')->controller(CheckoutController::class)->name('payment.callback')->method('paymentCallback');
    });
});

// API Routes (example)
//Route::prefix('/api', function () {
//    Route::group(['middleware' => 'api'], function () {
//        Route::get()->url('/products')->controller(PublicController::class)->name('api.products')->method('apiIndex');
//        Route::post()->url('/products')->controller(ProductController::class)->name('api.products.store')->method('apiStore');
//        Route::get()->url('/products/{id}')->controller(PublicController::class)
//            ->name('api.products.show')
//            ->where('id', '[0-9]+')
//            ->method('apiShow');
//    });
//});

// Test Routes (for development)
Route::prefix('/test', function () {
    Route::get()->url('/test')->controller(TestController::class)->name('test.index')->method('index');

    // Test route with multiple methods
    Route::match(['GET', 'POST'])->url('/multi')->controller(TestController::class)->name('test.multi')->method('multiMethod');

    // Test redirect route
    Route::redirect('/old-url', '/new-url', 301);

    // Test view route
    Route::view('/welcome', 'welcome', ['name' => 'User']);
});
