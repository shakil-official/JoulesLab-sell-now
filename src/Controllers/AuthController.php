<?php

namespace SellNow\Controllers;

use App\Core\Config\Helper;
use App\Core\Controller\Controller;
use App\Core\Route\Request;
use App\Core\Services\AuthService;
use App\Models\User;
use Exception;
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
    public function login(Request $request): void
    {
        $email = $request->input('email');
        $password = $request->input('password');

        //todo: validation need here

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

        Helper::redirect('login', [
            'success' => 'Login Successfully'
        ]);
    }

    public function registerForm()
    {
        echo $this->twig->render('auth/register.html.twig');
    }

    public function register()
    {
        if (empty($_POST['email']) || empty($_POST['password']))
            die("Fill all fields");

        // Raw SQL
        $sql = "INSERT INTO users (email, username, Full_Name, password) VALUES (?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        try {
            $stmt->execute([
                $_POST['email'],
                $_POST['username'],
                $_POST['fullname'],
                $_POST['password']
            ]);
        } catch (Exception $e) {
            die("Error registering: " . $e->getMessage());
        }

        header("Location: /login?msg=Registered successfully");
        exit;
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
