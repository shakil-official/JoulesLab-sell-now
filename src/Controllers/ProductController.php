<?php

namespace SellNow\Controllers;

use App\Core\Config\Helper;
use App\Core\Controller\Controller;
use JetBrains\PhpStorm\NoReturn;
use Psr\Http\Message\ServerRequestInterface;
use SellNow\Services\Product\ProductService;

class ProductController extends Controller
{
    /**
     */
    public function index(): \Psr\Http\Message\ResponseInterface
    {
        return $this->render('products/add', []);
    }

    #[NoReturn]
    public function store(ServerRequestInterface $request): void
    {
        $title = $this->getInput($request, 'title') ?? '';
        $price = $this->getInput($request, 'price') ?? '';

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
