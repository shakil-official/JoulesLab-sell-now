<?php

namespace SellNow\Controllers;

use App\Core\Config\Helper;
use App\Core\Controller\Controller;
use App\Core\Services\AuthService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use SellNow\Services\Cart\CartService;
use SellNow\Services\Payments\PaymentGatewayFactory;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class CheckoutController extends Controller
{
    /**
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws LoaderError
     */
    public function index(): \Psr\Http\Message\ResponseInterface
    {
        $cart = $_SESSION['cart'] ?? [];

        if (empty($cart)) {
            Helper::redirect('/cart', [
                'error' => 'Cart is empty'
            ]);
        }

        $total = (new CartService())->calculateTotal($cart);

        $_SESSION['order'] = [
            'total' => $total,
            'hash' => hash('sha256', serialize($cart)),
        ];

        $providers = array_map(
            fn($g) => $g->name(),
            PaymentGatewayFactory::all()
        );

        return $this->render('checkout/index', [
            'total' => $total,
            'providers' => $providers,
        ]);
    }



    public function process(ServerRequestInterface $request): void
    {
        if (empty($_SESSION['cart']) || empty($_SESSION['order'])) {
            Helper::redirect('/cart');
        }

        try {
            $gateway = PaymentGatewayFactory::make(
                $this->getInput($request, 'provider')
            );
        } catch (\Throwable) {
            http_response_code(400);
            exit('Invalid payment provider');
        }

        /*
         * Redirect to payment page
         * users can modify the total amount
         * for security reason we are not passing the total amount
         * */

        header('Location: /payment?provider=' . urlencode($gateway->name()));
        exit;


    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function payment(ServerRequestInterface $request): \Psr\Http\Message\ResponseInterface
    {

        if (empty($_SESSION['cart']) || empty($_SESSION['order'])) {
            Helper::redirect('/cart');
        }

        // check
        if (hash('sha256', serialize($_SESSION['cart'])) !== $_SESSION['order']['hash']) {
            http_response_code(400);
            exit('Order tampered');
        }

        $provider = $this->getInput($request, 'provider');

        try {
            $gateway = PaymentGatewayFactory::make($provider);
        } catch (\Throwable) {
            http_response_code(400);
            exit('Invalid payment provider');
        }

        $total = (new CartService())->calculateTotal($_SESSION['cart']);

        return $this->render('checkout/payment', [
            'provider' => $gateway->name(),
            'total' => $total,
        ]);
    }

    /**
     * @param ServerRequestInterface $request
     */
    public function success(ServerRequestInterface $request): \Psr\Http\Message\ResponseInterface
    {

        if (empty($_SESSION['cart']) || empty($_SESSION['order'])) {
            Helper::redirect('/cart');
        }

        $check = hash('sha256', serialize($_SESSION['cart'])) !== $_SESSION['order']['hash'];


        if ($check) {
            http_response_code(400);
            exit('Order integrity violation');
        }

        try {
            $gateway = PaymentGatewayFactory::make(
                $this->getInput($request, 'provider')
            );
        } catch (\Throwable) {
            http_response_code(400);
            exit('Invalid payment provider');
        }

        $this->logTransaction($gateway->name());

        session_regenerate_id(true);

        unset($_SESSION['cart'], $_SESSION['order']);

        CartService::clear();

        return $this->render('checkout/success', [
            'provider' => $gateway->name(),
        ]);
    }

    private function logTransaction(string $provider): void
    {
        $logFile = __DIR__ . '/../../storage/logs/transactions.log';

        if (!is_dir(dirname($logFile))) {
            mkdir(dirname($logFile), 0755, true);
        }

        $entry = sprintf(
            "%s - Payment via %s - User: %s\n",
            date('Y-m-d H:i:s'),
            $provider,
            AuthService::userId() ?? 'Guest'
        );

        file_put_contents($logFile, $entry, FILE_APPEND | LOCK_EX);
    }
}
