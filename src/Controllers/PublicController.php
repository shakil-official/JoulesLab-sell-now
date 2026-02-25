<?php

namespace SellNow\Controllers;

use App\Core\Config\Helper;
use App\Core\Controller\Controller;
use App\Core\Services\AuthService;
use Exception;
use Psr\Http\Message\ServerRequestInterface;
use SellNow\Models\Product;

class PublicController extends Controller
{
    /**
     * @throws Exception
     */
    public function profile(ServerRequestInterface $request): \Psr\Http\Message\ResponseInterface
    {
        $user = AuthService::user();

        if (!$user) {
            Helper::redirect('/', [
                'error' => 'You must be logged in to view this page'
            ]);
        }

        $products = Product::query()
            ->where([
                'user_id' => $user['id']
            ])->get();

        return $this->render('public/profile', [
            'seller' => $user,
            'products' => $products
        ]);
    }
}
