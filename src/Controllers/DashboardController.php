<?php

namespace SellNow\Controllers;

use App\Core\Config\Helper;
use App\Core\Controller\Controller;
use App\Core\Services\AuthService;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class DashboardController extends Controller
{
    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     */
    public function index(): void
    {
        $auth = AuthService::user();

        $this->render('dashboard', [
            'username' => $auth['username'],
            'success' => Helper::getMessage('success'),
            'error' => Helper::getMessage('error'),
        ]);
    }
}
