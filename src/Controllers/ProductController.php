<?php

namespace SellNow\Controllers;

use App\Core\Config\Helper;
use App\Core\Controller\Controller;
use App\Core\Route\Request;
use JetBrains\PhpStorm\NoReturn;
use SellNow\Services\Product\ProductService;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class ProductController extends Controller
{
    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     */
    public function index(): void
    {
        $this->render('products/add', []);
    }

    #[NoReturn]
    public function store(Request $request): void
    {
        $title = $request->input('title') ?? '';
        $price = $request->input('price') ?? '';

        if (!$title || !$price) {
            Helper::redirect('/dashboard', [
                'error' => 'Title and Price are required',
            ]);
        }

        $product = (new ProductService())->create($title, $price);

        if ($product) {
            Helper::redirect('/dashboard', [
                'success' => 'Product added successfully!'
            ]);
        }

        Helper::redirect('/dashboard', [
            'error' => 'Failed to save product',
        ]);
    }
}
