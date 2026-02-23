<?php

// Debug script to check registered routes
require_once __DIR__ . '/vendor/autoload.php';

use App\Core\Route\Request;
use App\Core\Route\Route;
use App\Core\Route\Router;
use App\Core\View\View;
use Twig\Loader\FilesystemLoader;
use Twig\Environment;

// Setup basic Twig
$loader = new FilesystemLoader(__DIR__ . '/templates');
$twig = new Environment($loader);
$view = new View($twig);

// Initialize router
$router = new Router($view);
Route::init($router);

// Load routes
require __DIR__ . '/src/Routes/web.php';

echo "=== REGISTERED ROUTES ===\n";
$routes = $router->getRoutes();

foreach ($routes as $i => $route) {
    echo ($i + 1) . ". {$route[0]} {$route[1]} -> {$route[2]['controller']}@{$route[2]['action']}\n";
}

echo "\n=== LOOKING FOR /cart ROUTES ===\n";
$cartRoutes = array_filter($routes, function($route) {
    return strpos($route[1], '/cart') !== false;
});

foreach ($cartRoutes as $i => $route) {
    echo "CART ROUTE: {$route[0]} {$route[1]} -> {$route[2]['controller']}@{$route[2]['action']}\n";
}

echo "\n=== TESTING DISPATCH ===\n";
$testRequest = new Request();
echo "Request URI: " . $testRequest->uri() . "\n";
echo "Request Method: " . $testRequest->method() . "\n";
