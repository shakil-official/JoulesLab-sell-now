<?php

namespace App\Core;

use App\Core\Container\Container;
use App\Core\Http\PsrRouter;
use App\Core\Http\Request;
use App\Core\Route\Route;
use App\Core\Route\Router;
use App\Core\Services\CsrfService;
use App\Core\Services\DatabaseService;
use App\Core\View\View;
use Illuminate\Database\Capsule\Manager as Capsule;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use ReflectionClass;
use ReflectionException;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\TwigFunction;

class Application
{
    private Container $container;
    private Router $legacyRouter;
    private PsrRouter $router;

    public function __construct()
    {
        // Start session immediately
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Initialize database connection first (before container)
        \App\Core\Database\Database::getInstance();
        
        $this->container = new Container();
        $this->bootstrap();
        $this->initializeServices();
    }

    private function bootstrap(): void
    {
        // Bind core services
        $this->container->singleton(View::class, function () {
            $loader = new FilesystemLoader(__DIR__ . '/../../templates');
            $twig = new Environment($loader, [
                'cache' => false,
                'debug' => true,
            ]);

            return new View($twig);
        });

        // Initialize legacy router for compatibility with existing routes
        $this->container->singleton(Router::class, function () {
            return new Router(
                $this->container->get(View::class),
                $this->container->get(CsrfService::class),
                $this->container
            );
        });

        $this->container->singleton(PsrRouter::class, function () {
            return new PsrRouter($this->container->get(View::class));
        });

        // Bind request
        $this->container->singleton(ServerRequestInterface::class, function () {
            return Request::fromGlobals();
        });

        // Bind services
        $this->container->singleton(DatabaseService::class, function () {
            return new DatabaseService();
        });

        $this->container->singleton(CsrfService::class, function () {
            return new CsrfService();
        });

        // Dynamically register all controllers
        $this->registerControllers();

        // Initialize routers
        $this->legacyRouter = $this->container->get(Router::class);
        $this->router = $this->container->get(PsrRouter::class);

        // Initialize Route class with legacy router
        Route::init($this->legacyRouter);
    }

    private function registerControllers(): void
    {
        // Define controller namespaces to scan
        $controllerNamespaces = [
            'App\\Controllers\\' => __DIR__ . '/../../app/Controllers',
            'SellNow\\Controllers\\' => __DIR__ . '/../../src/Controllers',
            'App\\Core\\Controller\\' => __DIR__ . '/Controller',
        ];

        foreach ($controllerNamespaces as $namespace => $directory) {
            if (!is_dir($directory)) {
                continue;
            }

            $files = glob($directory . '/*Controller.php');
            
            if ($files === false) {
                continue;
            }
            
            foreach ($files as $file) {
                $className = basename($file, '.php');
                $fullClassName = $namespace . $className;

                try {
                    if (class_exists($fullClassName)) {
                        $this->container->singleton($fullClassName, function ($container) use ($fullClassName) {
                            return new $fullClassName(
                                $container->get(View::class),
                                $container->get(Container::class)
                            );
                        });
                    }
                } catch (\Exception $e) {
                    // Log error but continue with other controllers
                    error_log("Failed to register controller {$fullClassName}: " . $e->getMessage());
                }
            }
        }
    }

    /**
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     * @throws ReflectionException
     */
    private function initializeServices(): void
    {
        // Initialize database connection using service
        // Database connection is now automatically handled by Database class
        // No need to manually initialize Model connection anymore

        // Ensure CSRF token is always available using service
        $this->container->get(CsrfService::class)->generate();

        // Setup Twig globals
        $view = $this->container->get(View::class);
        $reflection = new ReflectionClass($view);
        $twigProperty = $reflection->getProperty('twig');
        $twigProperty->setAccessible(true);
        $twig = $twigProperty->getValue($view);
        
        $twig->addGlobal('session', $_SESSION);
        $twig->addFunction(new TwigFunction('csrf', function () {
            return $this->container->get(CsrfService::class)->generate();
        }));
    }

    public function getContainer(): Container
    {
        return $this->container;
    }

    public function getRouter(): PsrRouter
    {
        return $this->container->get(PsrRouter::class);
    }

    public function run(): ResponseInterface
    {
        $request = $this->container->get(ServerRequestInterface::class);
        
        // Create custom request for compatibility with existing code
        $customRequest = Request::fromGlobals();
        
        // Store custom request as attribute for potential use in controllers
        $request = $request->withAttribute('custom_request', $customRequest);
        
        // Ensure the main request has the parsed body from the custom request
        $request = $request->withParsedBody($customRequest->getParsedBody());
        
        // Use legacy router for now since routes are defined using the old system
        $response = $this->legacyRouter->dispatch($request);

        // Emit response
        $this->emitResponse($response);

        return $response;
    }

    private function emitResponse(ResponseInterface $response): void
    {
        // Status line
        $statusLine = sprintf(
            'HTTP/%s %d %s',
            $response->getProtocolVersion(),
            $response->getStatusCode(),
            $response->getReasonPhrase()
        );
        header($statusLine, true, $response->getStatusCode());

        // Headers
        foreach ($response->getHeaders() as $name => $values) {
            foreach ($values as $value) {
                header(sprintf('%s: %s', $name, $value), false);
            }
        }

        // Body
        echo $response->getBody();
    }
}
