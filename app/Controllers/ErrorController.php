<?php

namespace App\Controllers;

use App\Core\Route\Request;
use App\Core\View\View;

class ErrorController
{
    protected View $view;

    public function __construct(View $view)
    {
        $this->view = $view;
    }

    public function notFound(Request $request): void
    {
        http_response_code(404);
        
        try {
            $this->view->render("errors/404", [
                'uri' => $request->uri(),
                'method' => $request->method()
            ]);
        } catch (\Twig\Error\LoaderError $e) {
            // Fallback if template doesn't exist
            echo '<!DOCTYPE html>
<html>
<head><title>404 - Page Not Found</title></head>
<body style="font-family: Arial, sans-serif; text-align: center; margin-top: 50px;">
    <h1 style="color: #17a2b8;">404 - Page Not Found</h1>
    <p>The page you requested could not be found.</p>
    <p>URL: ' . htmlspecialchars($request->uri()) . '</p>
    <p>Method: ' . htmlspecialchars($request->method()) . '</p>
    <hr>
    <a href="/dashboard" style="display: inline-block; padding: 10px 20px; background: #007bff; color: white; text-decoration: none; margin: 5px;">Go to Dashboard</a>
    <a href="/" style="display: inline-block; padding: 10px 20px; background: #6c757d; color: white; text-decoration: none; margin: 5px;">Go to Login</a>
</body>
</html>';
        }
    }

    public function methodNotAllowed(Request $request, array $allowedMethods): void
    {
        http_response_code(405);
        header('Allow: ' . implode(', ', $allowedMethods));
        
        try {
            $this->view->render("errors/405", [
                'uri' => $request->uri(),
                'method' => $request->method(),
                'allowedMethods' => $allowedMethods
            ]);
        } catch (\Twig\Error\LoaderError $e) {
            // Fallback if template doesn't exist
            echo '<!DOCTYPE html>
<html>
<head><title>405 - Method Not Allowed</title></head>
<body style="font-family: Arial, sans-serif; text-align: center; margin-top: 50px;">
    <h1 style="color: #ffc107;">405 - Method Not Allowed</h1>
    <p>The ' . htmlspecialchars($request->method()) . ' method is not allowed for this URL.</p>
    <p>Allowed methods: ' . htmlspecialchars(implode(', ', $allowedMethods)) . '</p>
    <p>URL: ' . htmlspecialchars($request->uri()) . '</p>
    <hr>
    <a href="/dashboard" style="display: inline-block; padding: 10px 20px; background: #007bff; color: white; text-decoration: none; margin: 5px;">Go to Dashboard</a>
    <a href="/" style="display: inline-block; padding: 10px 20px; background: #6c757d; color: white; text-decoration: none; margin: 5px;">Go to Login</a>
</body>
</html>';
        }
    }

    public function handle404(Request $request): void
    {
        $this->notFound($request);
    }
}
