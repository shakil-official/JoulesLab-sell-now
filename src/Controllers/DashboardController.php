<?php

namespace SellNow\Controllers;

use App\Core\Config\Helper;
use App\Core\Controller\Controller;
use App\Core\Services\AuthService;
use Psr\Http\Message\ResponseInterface;

class DashboardController extends Controller
{
    /**
     * @return ResponseInterface
     */
    public function index(): \Psr\Http\Message\ResponseInterface
    {
        $auth = AuthService::user();

        return $this->render('dashboard', [
            'username' => $auth['username'],
            'success' => Helper::getMessage('success'),
            'error' => Helper::getMessage('error'),
        ]);
    }
}
