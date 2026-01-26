<?php

namespace App\Controllers;

use App\Core\Controller\Controller;
use App\Core\Route\Request;

class TestController extends Controller
{
    public function index(Request $request): void
    {
        $request->input('name');

        $this->render('test/index', []);
    }
}
