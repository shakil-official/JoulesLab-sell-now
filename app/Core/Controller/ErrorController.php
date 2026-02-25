<?php

namespace App\Core\Controller;

use App\Core\View\View;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ErrorController extends Controller
{
    public function __construct(View $view, \App\Core\Container\Container $container)
    {
        parent::__construct($view, $container);
    }

    public function notFound(ServerRequestInterface $request): ResponseInterface
    {
        try {
            return $this->render("errors/404", [
                'uri' => $request->getUri()->getPath(),
                'method' => $request->getMethod()
            ]);
        } catch (\Twig\Error\LoaderError $e) {
            // Fallback if template doesn't exist
            $factory = new \Nyholm\Psr7\Factory\Psr17Factory();
            $response = $factory->createResponse(404);
            $response->getBody()->write('<!DOCTYPE html>
<html>
<head><title>404 - Page Not Found</title></head>
<body>
<h1>404 - Page Not Found</h1>
<p>The requested page could not be found.</p>
<p><a href="/">Go back to homepage</a></p>
</body>
</html>');
            return $response;
        }
    }

    public function methodNotAllowed(ServerRequestInterface $request, array $allowedMethods = []): ResponseInterface
    {
        // Try to get allowed methods from request attribute if not passed directly
        if (empty($allowedMethods)) {
            $allowedMethods = $request->getAttribute('allowedMethods', []);
        }

        try {
            return $this->render("errors/405", [
                'uri' => $request->getUri()->getPath(),
                'method' => $request->getMethod(),
                'allowedMethods' => $allowedMethods
            ]);
        } catch (\Twig\Error\LoaderError $e) {
            // Fallback if template doesn't exist
            $factory = new \Nyholm\Psr7\Factory\Psr17Factory();
            $response = $factory->createResponse(405);
            $response = $response->withHeader('Allow', implode(', ', $allowedMethods));
            $response->getBody()->write('<!DOCTYPE html>
<html>
<head><title>405 - Method Not Allowed</title></head>
<body>
<h1>405 - Method Not Allowed</h1>
<p>The requested method is not allowed for this URL.</p>
<p>Allowed methods: ' . implode(', ', $allowedMethods) . '</p>
<p><a href="/">Go back to homepage</a></p>
</body>
</html>');
            return $response;
        }
    }
}
