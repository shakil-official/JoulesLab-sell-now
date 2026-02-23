<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Config\Csrf;
use App\Core\Database\Model;
use App\Core\Route\Request;
use App\Core\Route\Route;
use App\Core\Route\Router;
use App\Core\View\View;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Loader\FilesystemLoader;


ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

// Basic Twig Setup (Global-ish)
$loader = new FilesystemLoader(__DIR__ . '/../templates');
$twig = new Environment($loader, ['debug' => true]);
$twig->addGlobal('session', $_SESSION);

$twig->addFunction(new \Twig\TwigFunction('csrf', function () {
    return Csrf::generate();
}));



Model::setConnection(
    \App\Core\Database\Database::getInstance()->getConnection()
);

$view = new View($twig);

$router = new Router($view);
// $router->enableCache(); // Disable route caching for debugging
Route::init($router);

require __DIR__ . '/../src/Routes/web.php';

try {
    $request = new Request();
    $router->dispatch($request);
} catch (LoaderError|RuntimeError|SyntaxError $e) {
    var_dump($e);

}
