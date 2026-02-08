# SellNow App Architecture Documentation

## 1. Structure

### Directory Organization
```
app/
├── Core/
│   ├── Config/
│   │   ├── Csrf.php          # CSRF protection utilities
│   │   ├── Env.php           # Environment configuration handler
│   │   └── Helper.php        # Common helper functions
│   ├── Contracts/
│   │   └── Authenticatable.php # Authentication contract interface
│   ├── Controller/
│   │   └── Controller.php    # Base controller class
│   ├── Database/
│   │   ├── Database.php      # Database connection (Singleton pattern)
│   │   └── Model.php         # Active Record pattern base model
│   ├── Route/
│   │   ├── Request.php       # HTTP request handling
│   │   ├── Route.php         # Route definition
│   │   ├── RouteDefinition.php # Route configuration
│   │   └── Router.php        # Route dispatcher
│   ├── Services/
│   │   └── AuthService.php   # Authentication service
│   └── View/
│       └── View.php          # Template rendering (Twig integration)
└── README.md
```

## 2. Design Patterns

### Singleton Pattern
**Location**: `Core/Database/Database.php`
**Why Used**: Ensures only one database connection exists throughout the application lifecycle, preventing resource waste and connection overhead.

```php
private static ?Database $instance = null;

public static function getInstance(): Database
{
    if (!self::$instance) {
        self::$instance = new Database();
    }
    return self::$instance;
}
```

### Active Record Pattern
**Location**: `Core/Database/Model.php`
**Why Used**: Provides an intuitive way to interact with database records where each model instance represents a row in the database table. Enables fluent query building and direct data manipulation.

```php
public static function find(int $id, $select = ['*'], $key = 'id'): ?array
{
    $instance = static::query();
    return $instance->select($select)->where($key, $id)->first();
}
```

### Dependency Injection
**Location**: `Core/Controller/Controller.php`, `Core/Route/Router.php`
**Why Used**: Promotes loose coupling and testability by injecting dependencies (like View) rather than creating them internally.

```php
public function __construct(View $view)
{
    $this->view = $view;
}
```

### Strategy Pattern
**Location**: `Core/Database/Database.php`
**Why Used**: Allows different database drivers (MySQL, PostgreSQL, SQLite) to be used interchangeably based on configuration.

```php
switch ($driver) {
    case 'mysql':
        $this->connection = new PDO("mysql:host=" . ($host) . ";dbname=" . ($database), $username, $_ENV['DB_PASSWORD'] ?? '');
        break;
    case 'pgsql':
        $this->connection = new PDO("pgsql:host=" . ($host) . ";dbname=" . ($database), $username, $password);
        break;
    default: // sqlite fallback
        $dbPath = __DIR__ . '/../../../database/database.sqlite';
        $this->connection = new PDO("sqlite:" . $dbPath);
}
```

### Contract/Interface Pattern
**Location**: `Core/Contracts/Authenticatable.php`
**Why Used**: Defines a contract for authentication, ensuring any model implementing it provides required authentication methods, promoting consistency and interchangeability.

```php
interface Authenticatable
{
    public static function find(int $id);
    public static function findByCredentials(array $credentials);
    public function getAuthId();
    public function getAuthPassword();
    public function getUsername();
}
```

### Front Controller Pattern
**Location**: `Core/Route/Router.php`
**Why Used**: Centralizes request handling through a single entry point, providing consistent request processing and middleware application.

```php
public function dispatch(Request $request): void
{
    // Route matching, middleware execution, controller instantiation
}
```

## 3. Process Flow and Lifecycle

### Application Bootstrap
1. **Environment Loading**: `Env.php` loads configuration from environment variables
2. **Database Connection**: `Database.php` establishes connection using Singleton pattern
3. **View Initialization**: Twig environment is set up in `View.php`
4. **Router Registration**: Routes are registered with the `Router.php`

### Request Lifecycle

#### 1. Request Reception
- `Request.php` captures HTTP method, URI, and input data
- Route parameters are extracted and stored

#### 2. Route Dispatch
```php
// Router::dispatch()
$method = $request->method();
$uri = $request->uri();
```

#### 3. Route Matching
- Router iterates through registered routes
- Pattern matching using regex conversion (`toRegex()`)
- Parameter extraction from URI

#### 4. Middleware Execution
```php
foreach ($route['middlewares'] ?? [] as $middlewareClass) {
    $middleware = new $middlewareClass();
    if (method_exists($middleware, 'handle')) {
        $middleware->handle($request);
    }
}
```

#### 5. CSRF Validation (POST requests)
```php
if ($method === 'POST') {
    if (! Csrf::validate($request->input('csrf'))) {
        http_response_code(403);
        $this->view->render('errors/403');
        return;
    }
    Csrf::forget(); // one-time token
}
```

#### 6. Controller Instantiation and Action Execution
```php
$controller = new $route['controller']($this->view);
call_user_func_array([$controller, $route['action']], [$request]);
```

#### 7. Response Generation
- **HTML Response**: `Controller::render()` method uses Twig templates
- **JSON Response**: `Controller::json()` method for API responses

### Authentication Flow

#### 1. Authentication Setup
```php
AuthService::use(UserModel::class); // Model must implement Authenticatable
```

#### 2. Login Attempt
```php
public static function attempt(array $credentials): bool
{
    $user = $model::findByCredentials($credentials);
    if (!$user || !password_verify($credentials['password'], $user->getAuthPassword())) {
        return false;
    }
    
    session_regenerate_id(true);
    $_SESSION[self::SESSION_KEY] = [
        'model' => $model,
        'id' => $user->getAuthId(),
        'user_id' => $user->getAuthId(),
        'username' => $user->getUsername(),
    ];
    return true;
}
```

#### 3. User Retrieval
```php
public static function user()
{
    if (!isset($_SESSION[self::SESSION_KEY])) {
        return null;
    }
    $model = $_SESSION[self::SESSION_KEY]['model'];
    return $model::find($_SESSION[self::SESSION_KEY]['id'], [
        'username', 'email', 'id'
    ]);
}
```

### Database Operations Flow

#### 1. Connection Establishment
- Singleton ensures single connection
- Driver selection based on environment config
- PDO with error mode exception

#### 2. Query Building (Fluent Interface)
```php
User::select(['id', 'username'])
    ->where('status', 'active')
    ->where('created_at', '>', '2024-01-01')
    ->limit(10)
    ->get();
```

#### 3. SQL Generation
- Dynamic query building based on method calls
- Prepared statements for security
- Parameter binding for injection prevention

### View Rendering Process

#### 1. Template Selection
```php
$this->view->render('users/index', $data);
```

#### 2. Flash Message Integration
```php
$flashData = [
    'success' => \App\Core\Config\Helper::getMessage('success'),
    'error'   => \App\Core\Config\Helper::getMessage('error'),
];
$data = array_merge($data, $flashData);
```

#### 3. Twig Rendering
- Template file: `users/index.html.twig`
- Automatic context passing
- Error handling for Twig exceptions

### Security Features

#### 1. CSRF Protection
- Token generation using `random_bytes(32)`
- Session-based token storage
- One-time token usage
- Validation using `hash_equals()`

#### 2. Password Security
- `password_hash()` with `PASSWORD_DEFAULT`
- `password_verify()` for authentication
- Session regeneration on login/logout

#### 3. SQL Injection Prevention
- Prepared statements throughout
- Parameter binding in Model class
- Type casting for safety

### Error Handling

#### 1. Database Errors
- Exception handling in Database connection
- PDO error mode set to exceptions

#### 2. Route Errors
- 404 handling with custom error pages
- 403 handling for CSRF violations

#### 3. View Errors
- Twig exception handling
- Loader, Runtime, and Syntax error catching

### Helper Utilities

#### 1. File Upload
```php
Helper::uploadFile('image', '/path/to/uploads', 'prefix_');
```

#### 2. URL Slugs
```php
Helper::slug('Product Title'); // product-title-1234
```

#### 3. Redirects with Flash Messages
```php
Helper::redirect('/dashboard', ['success' => 'Login successful']);
```

This architecture provides a solid foundation for a modern PHP web application with proper separation of concerns, security measures, and extensibility.

## 4. Complete Route System Code

### Request.php - HTTP Request Handler
```php
<?php

namespace App\Core\Route;

class Request
{
    protected array $routeParams = [];
    
    public function uri(): string
    {
        return '/' . trim(
                parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH),
                '/'
            );
    }

    public function method(): string
    {
        return $_SERVER['REQUEST_METHOD'];
    }

    public function setRouteParams(array $params): void
    {
        $this->routeParams = $params;
    }

    public function input(string $key, $default = null)
    {
        return $this->routeParams[$key] ?? $_REQUEST[$key] ?? $default;
    }
}
```

**Purpose**: 
- Extracts URI from `$_SERVER['REQUEST_URI']`
- Gets HTTP method from `$_SERVER['REQUEST_METHOD']`
- Merges route parameters with request input (`$_REQUEST`)
- Provides unified access to URL parameters and form data

---

### Route.php - Static Route Facade
```php
<?php

namespace App\Core\Route;

class Route
{
    protected static Router $router;

    public static function init(Router $router): void
    {
        self::$router = $router;
    }

    public static function get(): RouteDefinition
    {
        return new RouteDefinition('GET', self::$router);
    }

    public static function post(): RouteDefinition
    {
        return new RouteDefinition('POST', self::$router);
    }
}
```

**Purpose**:
- Provides static interface for route registration
- Initializes with Router instance
- Returns RouteDefinition builders for GET/POST methods
- Enables fluent route definition syntax

---

### RouteDefinition.php - Fluent Route Builder
```php
<?php

namespace App\Core\Route;

class RouteDefinition
{
    protected string $httpMethod;
    protected Router $router;
    protected array $definition = [];

    public function __construct(string $httpMethod, Router $router)
    {
        $this->httpMethod = $httpMethod;
        $this->router     = $router;
        $this->definition['middlewares'] = [];
    }

    public function url(string $uri): self
    {
        $this->definition['uri'] = $uri;
        preg_match_all('#\{([^}]+)\}#', $uri, $matches);
        $this->definition['params'] = $matches[1] ?? [];

        return $this;
    }

    public function controller(string $controller): self
    {
        if (!class_exists($controller)) {
            throw new \InvalidArgumentException("Controller not found: {$controller}");
        }

        $this->definition['controller'] = $controller;
        return $this;
    }

    public function method(string $method): void
    {
        $this->router->register(
            $this->httpMethod,
            $this->definition['uri'],
            $this->definition['controller'],
            $method,
            $this->definition['params'] ?? [],
            $this->definition['middlewares'] ?? []
        );
    }

    public function middleware(string|array $middleware): self
    {
        if (is_array($middleware)) {
            $this->definition['middlewares'] = array_merge(
                $this->definition['middlewares'],
                $middleware
            );
        } else {
            $this->definition['middlewares'][] = $middleware;
        }

        return $this;
    }
}
```

**Purpose**:
- Implements fluent interface for route definition
- Extracts parameter names from URI patterns using regex
- Validates controller existence
- Registers route with Router after complete definition
- Supports multiple middleware assignment

---

### Router.php - Route Dispatcher
```php
<?php

namespace App\Core\Route;

use App\Core\Config\Csrf;
use App\Core\View\View;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class Router
{
    protected array $routes = [];
    protected View $view;

    public function __construct(View $view)
    {
        $this->view = $view;
    }

    public function register(
        string $httpMethod,
        string $uri,
        string $controller,
        string $action,
        array $params = [],
        array $middlewares = []
    ): void
    {
        $this->routes[$httpMethod][] = [
            'pattern' => $this->toRegex($uri),
            'controller' => $controller,
            'action' => $action,
            'params'     => $params,
            'middlewares'     => $middlewares,
        ];
    }

    protected function toRegex(string $uri): string
    {
        $pattern = preg_replace('#\{[^/]+\}#', '([^/]+)', $uri);
        return '#^' . $pattern . '$#';
    }

    public function dispatch(Request $request): void
    {
        $method = $request->method();
        $uri = $request->uri();

        foreach ($this->routes[$method] ?? [] as $route) {
            if (preg_match($route['pattern'], $uri, $matches)) {

                array_shift($matches); // remove full match

                $paramNames = $route['params'] ?? [];
                $params = [];
                foreach ($paramNames as $i => $name) {
                    $params[$name] = $matches[$i] ?? null;
                }

                $request->setRouteParams($params);

                if ($method === 'POST') {
                    if (! Csrf::validate($request->input('csrf'))) {
                        http_response_code(403);
                        $this->view->render('errors/403');
                        return;
                    }

                    Csrf::forget(); // one-time token
                }

                foreach ($route['middlewares'] ?? [] as $middlewareClass) {
                    $middleware = new $middlewareClass();
                    if (method_exists($middleware, 'handle')) {
                        $middleware->handle($request);
                    }
                }

                $controller = new $route['controller']($this->view);

                call_user_func_array([$controller, $route['action']], [$request]);

                return;
            }
        }

        http_response_code(404);
        $this->view->render("errors/404");
    }
}
```

**Purpose**:
- Stores registered routes by HTTP method
- Converts URI patterns to regex for matching
- Dispatches requests to matching controllers
- Handles CSRF validation for POST requests
- Executes middleware chain before controller
- Manages 404/403 error responses

---

## Route System Usage Examples

### Basic Route Registration
```php
// Initialize router
$router = new Router($view);
Route::init($router);

// GET route
Route::get()
    ->url('/users/{id}')
    ->controller(UserController::class)
    ->method('show');

// POST route with middleware
Route::post()
    ->url('/login')
    ->controller(AuthController::class)
    ->middleware(AuthMiddleware::class)
    ->method('login');
```

### Route Matching Process
1. **Pattern Conversion**: `/users/{id}` → `#^/users/([^/]+)$#`
2. **Parameter Extraction**: `/users/123` matches, extracts `id = 123`
3. **Request Population**: Sets route parameters in Request object
4. **Security Check**: Validates CSRF token for POST requests
5. **Middleware Execution**: Runs all registered middleware
6. **Controller Dispatch**: Instantiates controller and calls method

### Advanced Features
- **Parameter Validation**: Route parameters automatically extracted and typed
- **Middleware Chain**: Multiple middleware supported per route
- **CSRF Protection**: Automatic token validation for POST requests
- **Error Handling**: Built-in 404/403 response rendering
- **Fluent Interface**: Clean, readable route definition syntax

This routing system provides a robust foundation for web application request handling with modern security features and flexible middleware support.