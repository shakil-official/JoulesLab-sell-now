<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Application;

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

try {
    // Initialize the application
    $app = new Application();

    // Load routes
    require __DIR__ . '/../src/Routes/web.php';

    // Run the application
    $app->run();
} catch (Throwable $e) {
    // Handle all errors including fatal errors
    http_response_code(500);
    
    if (ini_get('display_errors')) {
        echo '<h1>Application Error</h1>';
        echo '<h2>' . get_class($e) . '</h2>';
        echo '<p><strong>Message:</strong> ' . htmlspecialchars($e->getMessage()) . '</p>';
        echo '<p><strong>File:</strong> ' . htmlspecialchars($e->getFile()) . ':' . $e->getLine() . '</p>';
        
        if (ini_get('display_startup_errors')) {
            echo '<h3>Stack Trace:</h3>';
            echo '<pre>' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
        }
    } else {
        echo '<h1>500 Internal Server Error</h1>';
        echo '<p>Something went wrong. Please try again later.</p>';
    }
    
    // Log the error
    error_log("Application Error: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine());
}
