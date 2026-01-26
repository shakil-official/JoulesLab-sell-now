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

        Product::create([
            'user_id' => 1,
            'title' => 'test',
            'slug' => 'test',
            'description' => 'this is test',
            'price' => 23,
            'image_path' => 'hello/hh.png',
            'file_path' => 'hello/hh.png',
            'is_active' => 1
        ]);

        $data = Product::query()
            ->where([

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
