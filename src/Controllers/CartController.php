<?php

namespace SellNow\Controllers;

use App\Core\Controller\Controller;
use App\Core\Route\Request;
use Exception;
use JetBrains\PhpStorm\NoReturn;
use SellNow\Models\Product;
use SellNow\Services\Cart\CartService;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class CartController extends Controller
{

    /**
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws LoaderError
     */
    public function index(): void
    {
        $cart = $_SESSION['cart'] ?? [];
        $total = 0;
        foreach ($cart as $item) {
            $total += $item['price'] * $item['quantity'];
        }

        $this->render('cart/index', [
            'cart' => $cart,
            'total' => $total
        ]);
    }

    /**
     * @throws Exception
     */
    #[NoReturn]
    public function add(Request $request): void
    {
        $productId = (int) $request->input('product_id');
        $quantity  = (int) $request->input('quantity', 1);

        $result = CartService::add($productId, $quantity);

        header('Content-Type: application/json');
        echo json_encode($result);
        exit;
    }

    public function clear()
    {
        unset($_SESSION['cart']);
        header("Location: /cart");
        exit;
    }
}
