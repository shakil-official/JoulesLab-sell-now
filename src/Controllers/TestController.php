<?php

namespace App\Controllers;

use App\Core\Controller\Controller;
use App\Core\Route\Request;
use App\Models\Product;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class TestController extends Controller
{
    /**
     * @throws \Exception
     */
    public function index(Request $request): void
    {
        $name = $request->input('name');

        $data = Product::query()
            ->where([
                'product_id' => 2
            ])->get();

        echo '<pre>';
        print_r($data);

        try {
            $this->render('test/index', [
                'name' => $name
            ]);
        } catch (LoaderError|RuntimeError|SyntaxError $e) {

        }
    }
}
