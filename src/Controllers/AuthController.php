<?php

namespace SellNow\Controllers;

use App\Core\Controller\Controller;

class AuthController  extends Controller
{
    public function loginView(): void
    {
//        if (isset($_SESSION['user_id'])) {
//            header("Location: /dashboard");
//            exit;
//        }


        $this->render('auth/login', [

        ]);
    }

    public function loginForm()
    {

        echo $this->twig->render('auth/login.html.twig');
    }

    public function login()
    {
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        // Raw SQL, no Model
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($user && $password == $user['password']) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            header("Location: /dashboard");
            exit;
        } else {
            header("Location: /login?error=Invalid credentials");
            exit;
        }
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
        } catch (\Exception $e) {
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
