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
Route::init($router);

require __DIR__ . '/../src/Routes/web.php';

try {
    $router->dispatch(new Request());
} catch (LoaderError|RuntimeError|SyntaxError $e) {

}
