<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Database\Model;
use SellNow\Config\Database;
use Twig\Loader\FilesystemLoader;
use Twig\Environment;

session_start();

// Basic Twig Setup (Global-ish)
$loader = new FilesystemLoader(__DIR__ . '/../templates');
$twig = new Environment($loader, ['debug' => true]);
$twig->addGlobal('session', $_SESSION);


/*
 *  Route Bootstrapping
 */

use App\Core\Route\Router;
use App\Core\Route\Route;
use App\Core\Route\Request;
use App\Core\View\View;

Model::setConnection(
    \App\Core\Database\Database::getInstance()->getConnection()
);

$view = new View($twig);

$router = new Router($view);
Route::init($router);

require __DIR__ . '/../src/Routes/web.php';

try {
    $router->dispatch(new Request());
} catch (\Twig\Error\LoaderError|\Twig\Error\RuntimeError|\Twig\Error\SyntaxError $e) {

}


// Database Connection
$db = Database::getInstance()->getConnection();

// Basic Routing (Switch Statement)
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

// Simple helper for redirection
function redirect($url)
{
    header("Location: $url");
    exit;
}

// Router
switch ($uri) {

    case '/logout':
        session_destroy();
        redirect('/');
        break;


    case '/products/add':
        require_once __DIR__ . '/../src/Controllers/ProductController.php';
        $prod = new \SellNow\Controllers\ProductController($twig, $db);
        if ($method === 'POST')
            $prod->store();
        else
            $prod->create();
        break;

    case '/cart':
        require_once __DIR__ . '/../src/Controllers/CartController.php';
        $cart = new \SellNow\Controllers\CartController($twig, $db);
        $cart->index();
        break;

    case '/cart/add':
        require_once __DIR__ . '/../src/Controllers/CartController.php';
        $cart = new \SellNow\Controllers\CartController($twig, $db);
        $cart->add();
        break;

    case '/cart/clear':
        require_once __DIR__ . '/../src/Controllers/CartController.php';
        $cart = new \SellNow\Controllers\CartController($twig, $db);
        $cart->clear();
        break;

    case '/checkout':
        require_once __DIR__ . '/../src/Controllers/CheckoutController.php';
        $checkout = new \SellNow\Controllers\CheckoutController($twig, $db);
        $checkout->index();
        break;

    case '/checkout/process':
        require_once __DIR__ . '/../src/Controllers/CheckoutController.php';
        $checkout = new \SellNow\Controllers\CheckoutController($twig, $db);
        $checkout->process();
        break;

    case '/payment':
        require_once __DIR__ . '/../src/Controllers/CheckoutController.php';
        $checkout = new \SellNow\Controllers\CheckoutController($twig, $db);
        $checkout->payment();
        break;

    case '/checkout/success':
        require_once __DIR__ . '/../src/Controllers/CheckoutController.php';
        $checkout = new \SellNow\Controllers\CheckoutController($twig, $db);
        $checkout->success();
        break;

    // Default: Dynamic Routes (Messy)
    default:
        // Check for public profile /username
        // Imperfect: Assuming anything else is a username
        $parts = explode('/', trim($uri, '/'));

        if (count($parts) == 1 && !empty($parts[0])) {
            require_once __DIR__ . '/../src/Controllers/PublicController.php';
            $pub = new \SellNow\Controllers\PublicController($twig, $db);
            $pub->profile($parts[0]);
        } elseif (count($parts) == 2 && $parts[1] == 'products') {
            // /username/products -> redirect to profile
            redirect('/' . $parts[0]);
        } else {
            http_response_code(404);
            echo "404 Not Found";
        }
        break;
}

