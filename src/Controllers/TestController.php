<?php

namespace App\Controllers;

use App\Core\Controller\Controller;
use Psr\Http\Message\ServerRequestInterface;
use SellNow\Models\Product;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class TestController extends Controller
{
    /**
     * @throws \Exception
     */
    public function index(ServerRequestInterface $request): \Psr\Http\Message\ResponseInterface
    {
        $name = $this->getInput($request, 'name', 'Test User');

        return $this->render('test/index', [
            'name' => $name,
            'message' => 'Hello from TestController!',
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }

    public function multiMethod(ServerRequestInterface $request): \Psr\Http\Message\ResponseInterface
    {
        $method = $request->getMethod();
        $name = $this->getInput($request, 'name', 'Multi Method Test');

        return $this->render('test/multi', [
            'method' => $method,
            'name' => $name,
            'message' => 'This route handles multiple HTTP methods!'
        ]);
    }
}
