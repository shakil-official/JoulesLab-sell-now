<?php

namespace SellNow\Services\Cart;

use App\Core\Services\AuthService;
use Exception;
use SellNow\Models\Product;

class CartService
{
    /**
     * Add a product to the cart
     *
     * @param int $productId
     * @param int $quantity
     * @return array Response with status, cart count, and items
     * @throws Exception
     */
    public static function add(int $productId, int $quantity = 1): array
    {
        if (!$productId) {
            return [
                'status' => 'error',
                'message' => 'Product ID missing'
            ];
        }

        $product = Product::select(['product_id', 'title', 'price'])
            ->where([
                'product_id' => $productId,
                'user_id' => AuthService::userId()
            ])
            ->first();

        if (!$product) {
            return [
                'status' => 'error',
                'message' => 'Product not found'
            ];
        }

        // Initialize cart session
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }

        // Check if product already exists in cart
        $found = false;
        foreach ($_SESSION['cart'] as &$item) {
            if ($item['product_id'] == $product['product_id']) {
                $item['quantity'] += $quantity;
                $found = true;
                break;
            }
        }

        // If product not in cart, add it
        if (!$found) {
            $_SESSION['cart'][] = [
                'product_id' => $product['product_id'],
                'title' => $product['title'],
                'price' => $product['price'],
                'quantity' => $quantity
            ];
        }

        $_SESSION['cart_count'] = count($_SESSION['cart']);

        return [
            'status' => 'success',
            'count' => count($_SESSION['cart']),
            'cart' => $_SESSION['cart']
        ];
    }


    public static function clear(): void
    {
        unset($_SESSION['cart']);
        $_SESSION['cart_count'] = 0;
    }

    public function calculateTotal(array $cart): float
    {
        return array_reduce(
            $cart,
            fn ($sum, $item) => $sum + ($item['price'] * $item['quantity']),
            0
        );
    }

}
