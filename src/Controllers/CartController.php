<?php

namespace SellNow\Controllers;

use App\Core\Config\Helper;
use App\Core\Controller\Controller;
use Exception;
use JetBrains\PhpStorm\NoReturn;
use Psr\Http\Message\ServerRequestInterface;
use SellNow\Services\Cart\CartService;

class CartController extends Controller
{

    /**
     */
    public function index(): \Psr\Http\Message\ResponseInterface
    {
        $cart = $_SESSION['cart'] ?? [];
        $total = 0;
        foreach ($cart as $item) {
            $total += $item['price'] * $item['quantity'];
        }

        return $this->render('cart/index', [
            'cart' => $cart,
            'total' => $total
        ]);
    }

    /**
     * @throws Exception
     */
    #[NoReturn]
    public function add(ServerRequestInterface $request): void
    {
        $productId = (int)$this->getInput($request, 'product_id');
        $quantity = (int)$this->getInput($request, 'quantity', 1);

        $result = CartService::add($productId, $quantity);

        header('Content-Type: application/json');
        echo json_encode($result);
        exit;
    }

    /**
     * @return void
     */
    #[NoReturn]
    public function clear(): void
    {
        CartService::clear();

        Helper::redirect('/cart', [
            'success' => 'Cart cleared successfully'
        ]);
    }
}
