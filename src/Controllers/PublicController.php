<?php

namespace SellNow\Controllers;

use App\Core\Config\Helper;
use App\Core\Controller\Controller;
use App\Core\Route\Request;
use App\Core\Services\AuthService;
use Exception;
use SellNow\Models\Product;

class PublicController extends Controller
{
    /**
     * @throws Exception
     */
    public function profile(Request $request): void
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

        $this->render('public/profile', [
            'seller' => $user,
            'products' => $products
        ]);
    }
}
