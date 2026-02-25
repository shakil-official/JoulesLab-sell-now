<?php

namespace SellNow\Controllers;

use App\Core\Config\Helper;
use App\Core\Controller\Controller;
use App\Core\Services\AuthService;
use Exception;
use JetBrains\PhpStorm\NoReturn;
use Psr\Http\Message\ServerRequestInterface;
use SellNow\Models\User;
use SellNow\Services\Cart\CartService;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class AuthController extends Controller
{
    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     */
    public function loginView(): \Psr\Http\Message\ResponseInterface
    {
        //todo: need redirect if login

        return $this->render('auth/login', [
            'success' => Helper::getMessage('success'),
            'error' => Helper::getMessage('error'),
        ]);
    }


    /**
     * @throws Exception
     */
    #[NoReturn]
    public function login(ServerRequestInterface $request): void
    {
        $email = $this->getInput($request, 'email') ?? '';
        $password = $this->getInput($request, 'password') ?? '';

        if (!$email || !$password) {
            Helper::redirect('/', [
                'error' => 'All fields are required',
            ]);
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Helper::redirect('/', [
                'error' => 'Invalid email'
            ]);
        }

        AuthService::use(User::class);

        $auth = AuthService::attempt([
            'email' => $email,
            'password' => $password,
        ]);

        if (!$auth) {
            Helper::redirect('/', [
                'error' => 'Invalid credentials'
            ]);
        }

        Helper::redirect('/dashboard', [
            'success' => 'Login Successfully'
        ]);
    }

    /**
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws LoaderError
     */
    public function registerForm(): \Psr\Http\Message\ResponseInterface
    {
        return $this->render('auth/register', [
            'success' => Helper::getMessage('success'),
            'error' => Helper::getMessage('error'),
        ]);
    }

    public function register(ServerRequestInterface $request): void
    {
        $email = $this->getInput($request, 'email') ?? '';
        $password = $this->getInput($request, 'password') ?? '';
        $username = $this->getInput($request, 'username') ?? '';
        $full_name = $this->getInput($request, 'fullname') ?? '';

        if (!$email || !$password || !$username || !$full_name) {
            Helper::redirect('/register', [
                'error' => 'All fields are required',
            ]);
        }

        $password = Helper::hashPassword($password);

        try {

            if (User::query()->where([
                'email' => $email
            ])->first()) {
                Helper::redirect('/register', [
                    'error' => 'User already exits!!',
                ]);
            }

            User::create([
                'email' => $email,
                'username' => $username,
                'full_name' => $full_name,
                'password' => $password,
            ]);
        } catch (\Exception $e) {
            Helper::redirect('/register', [
                'error' => 'Registration failed',
            ]);
        }

        Helper::redirect('/', [
            'success' => 'Registered successfully. Please login.'
        ]);
    }


    #[NoReturn]
    public function logout(): void
    {
        AuthService::logout();
        CartService::clear();

        Helper::redirect('/', [
            'success' => 'Logout successfully. Please login.'
        ]);

    }

}
