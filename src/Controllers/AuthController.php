<?php

namespace SellNow\Controllers;

use App\Core\Config\Helper;
use App\Core\Controller\Controller;
use App\Core\Route\Request;
use App\Core\Services\AuthService;
use App\Models\User;
use Exception;
use JetBrains\PhpStorm\NoReturn;
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
    public function loginView(): void
    {
        //todo: need redirect if login

        $this->render('auth/login', [
            'success' => Helper::getMessage('success'),
            'error' => Helper::getMessage('error'),
        ]);
    }


    /**
     * @throws Exception
     */
    #[NoReturn]
    public function login(Request $request): void
    {
        $email = $request->input('email') ?? '';
        $password = $request->input('password') ?? '';

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
    public function registerForm(): void
    {
        $this->render('auth/register', [
            'success' => Helper::getMessage('success'),
            'error' => Helper::getMessage('error'),
        ]);
    }

    public function register(Request $request): void
    {
        $email = $request->input('email') ?? '';
        $password = $request->input('password') ?? '';
        $username = $request->input('username') ?? '';
        $full_name = $request->input('fullname') ?? '';

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

    public function dashboard()
    {
        if (!isset($_SESSION['user_id']))
            header("Location: /login");

        echo $this->twig->render('dashboard.html.twig', [
            'username' => $_SESSION['username']
        ]);
    }
}
