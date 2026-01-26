<?php

namespace App\Controllers;

use App\Core\Route\Request;

class TestController
{
    public function index(Request $request): void
    {
        echo 'Test route is working';
    }
}
