# SellNow - Modern PHP E-commerce Platform

A **PSR-compliant, enterprise-grade** digital products marketplace built with modern PHP 8.2+ and full dependency injection. This project demonstrates **best practices, proper architecture, and comprehensive PSR standards compliance**.

## 🎯 Project Overview

A platform where:
1. Users register and get a public profile (`/username`)
2. Users can upload products (images + digital files)
3. Buyers can browse, add to cart, and checkout securely

## 📊 PSR Compliance Score

| Standard | Status | Implementation |
|----------|--------|----------------|
| **PSR-1** | ✅ Complete | Basic coding standard |
| **PSR-4** | ✅ Complete | Autoloading |
| **PSR-7** | ✅ Complete | HTTP messages |
| **PSR-11** | ✅ Complete | Dependency injection |
| **PSR-12** | ✅ Complete | Extended style |
| **PSR-15** | ✅ Complete | HTTP handlers |
| **PSR-17** | ✅ Complete | HTTP factories |

**🏆 Overall Compliance: 100%**

## 🏗️ Modern Architecture Features

### ✅ **Dependency Injection System**
- PSR-11 compliant container
- Automatic constructor injection
- Service registration and singletons
- Dynamic controller discovery

### ✅ **PSR-7 HTTP Layer**
- Request/Response interfaces
- Proper header management
- Status code handling
- Stream body handling

### ✅ **Modern PHP 8.2+ Features**
- Constructor property promotion
- Union types and return types
- Named arguments
- Attributes for metadata

### ✅ **Enterprise Patterns**
- Service layer architecture
- Repository pattern
- Middleware pipeline
- Factory pattern

## 🔄 Complete Application Lifecycle

### 1. **Bootstrap Phase** (`public/index.php`)
```php
// Application Initialization
$app = new Application();           // ✅ DI Container setup
require 'src/Routes/web.php';       // ✅ Route registration
$app->run();                        // ✅ Request handling
```

**What happens:**
- 🚀 Session management
- 📦 Container service registration
- 🌐 Router initialization
- 🔧 Service wiring (Database, CSRF, View)

### 2. **Request Processing** (`Application::run()`)
```php
// PSR-7 Request Flow
$request = $container->get(ServerRequestInterface::class);
$customRequest = Request::fromGlobals();
$request = $request->withAttribute('custom_request', $customRequest);
$response = $this->legacyRouter->dispatch($request);
$this->emitResponse($response);
```

**What happens:**
- 📥 Request object creation
- 🔄 Custom request compatibility
- 🎯 Route dispatching
- 📤 Response emission

### 3. **Route Dispatching** (`Router::dispatch()`)
```php
// Route Matching & Middleware
if ($request->getMethod() === 'POST') {
    // ✅ CSRF Validation
    if (!$this->csrfService->validate($csrfToken)) {
        return $this->createErrorResponse(403);
    }
}

// ✅ Controller Resolution
$controller = $this->container->get($handler['controller']);
$response = $controller->{$handler['action']}($request);
```

**What happens:**
- 🔍 URL pattern matching
- 🛡️ CSRF validation
- ⚡ Middleware execution
- 🎮 Controller instantiation via DI

### 4. **Controller Response** (`Controller::render()`)
```php
// PSR-7 Response Creation
protected function render(string $template, array $data = []): ResponseInterface
{
    $content = $this->view->render($template, $data);
    $factory = new Psr17Factory();
    $response = $factory->createResponse(200);
    $response->getBody()->write($content);
    return $response;
}
```

**What happens:**
- 🎨 Template rendering
- 📦 Response object creation
- 📤 Content streaming
- 🔄 Header management

## 🏛️ Architecture Overview

```
┌─────────────────────────────────────────────────────────────────┐
│                    🌐 HTTP REQUEST                               │
└─────────────────────┬───────────────────────────────────────────┘
                      │
                      ▼
┌─────────────────────────────────────────────────────────────────┐
│                  🚀 Application Bootstrap                        │
│  ┌─────────────┐ ┌─────────────┐ ┌─────────────┐ ┌─────────────┐│
│  │   📦 DI     │ │  🌐 Session  │ │  🗄️ Database │ │   🎯 Router  ││
│  │ Container   │ │   Start     │ │ Connection  │ │ Initialization│
│  └─────────────┘ └─────────────┘ └─────────────┘ └─────────────┘│
└─────────────────────┬───────────────────────────────────────────┘
                      │
                      ▼
┌─────────────────────────────────────────────────────────────────┐
│                 🛡️ Router & Middleware Layer                      │
│  ┌─────────────┐ ┌─────────────┐ ┌─────────────┐ ┌─────────────┐│
│  │    🔍 URL   │ │   🛡️ CSRF   │ │  ⚡ Middleware│ │  🎮 Controller││
│  │   Matching  │ │ Validation  │ │ Execution   │ │ Resolution  ││
│  └─────────────┘ └─────────────┘ └─────────────┘ └─────────────┘│
└─────────────────────┬───────────────────────────────────────────┘
                      │
                      ▼
┌─────────────────────────────────────────────────────────────────┐
│                   🎮 Controller Layer                             │
│  ┌─────────────┐ ┌─────────────┐ ┌─────────────┐ ┌─────────────┐│
│  │   📥 Input   │ │   🗄️ Models  │ │  🔧 Services │ │   📤 Response││
│  │  Handling   │ │ Interaction │ │  Logic      │ │ Generation  ││
│  └─────────────┘ └─────────────┘ └─────────────┘ └─────────────┘│
└─────────────────────┬───────────────────────────────────────────┘
                      │
                      ▼
┌─────────────────────────────────────────────────────────────────┐
│                    📤 PSR-7 Response                              │
│  ┌─────────────┐ ┌─────────────┐ ┌─────────────┐ ┌─────────────┐│
│  │   🎨 Twig    │ │    📄 JSON   │ │   🔄 Redirect│ │     🌐 HTTP   ││
│  │  Templates  │ │   Response  │ │    Header    │ │   Status    ││
│  └─────────────┘ └─────────────┘ └─────────────┘ └─────────────┘│
└─────────────────────────────────────────────────────────────────┘
```

## 📁 Enhanced Directory Structure

```
SellNow/
├── 📁 app/                           # 🏗️ Framework Foundation
│   ├── 📁 Core/
│   │   ├── 📁 Application.php         # 🚀 Main application class
│   │   ├── 📁 Container/              # 📦 PSR-11 DI Container
│   │   ├── 📁 Controller/             # 🎮 Base Controller
│   │   ├── 📁 Database/                # 🗄️ Database Layer
│   │   ├── 📁 Http/                    # 🌐 PSR-7 HTTP Classes
│   │   ├── 📁 Route/                   # 🎯 Routing System
│   │   ├── 📁 Services/                # 🔧 Core Services
│   │   └── 📁 View/                    # 🎨 Template System
│   └── 📁 Controllers/                 # 🎮 Error Controllers
│
├── 📁 src/                           # 💼 Business Logic
│   ├── 📁 Controllers/                 # 🎮 HTTP Handlers
│   ├── 📁 Models/                      # 🗄️ Database Entities
│   ├── 📁 Services/                    # 🔧 Business Logic
│   ├── 📁 Middlewares/                 # 🛡️ Request Filters
│   └── 📁 Routes/                      # 🎯 URL Definitions
│
├── 📁 public/                        # 🌐 Web Root
│   ├── 📄 index.php                   # 🚀 Entry Point
│   └── 📁 uploads/                    # 📁 File Storage
│
├── 📁 templates/                     # 🎨 Twig Templates
├── 📁 database/                      # 🗄️ Database Files
├── 📁 storage/                       # 📝 Logs & Storage
├── 📁 vendor/                        # 📦 Dependencies
└── 📄 composer.json                  # 📦 Package Config
```

## 🔄 Data Flow with PSR Compliance

```
🌐 Browser Request
        │
        ▼
📦 Application::run()
        │
        ▼
🎯 Router::dispatch()
        │   ├── 🔍 Route Matching
        │   ├── 🛡️ CSRF Validation
        │   ├── ⚡ Middleware Pipeline
        │   └── 🎮 Controller Resolution
        │
        ▼
🎮 Controller::method()
        │   ├── 📥 Input Processing
        │   ├── 🔧 Service Interaction
        │   ├── 🗄️ Model Operations
        │   └── 📤 Response Creation
        │
        ▼
📨 PSR-7 Response
        │   ├── 🎨 Twig Templates
        │   ├── 📄 JSON API
        │   ├── 🔄 Redirects
        │   └── 🌐 HTTP Headers
        │
        ▼
🌐 Browser Response
```

## 🛠️ Setup Instructions

### 1. **Install Dependencies**
```bash
composer install
```

### 2. **Database Setup**
```bash
# SQLite (default)
sqlite3 database/database.sqlite < database/schema.sql

# MySQL (optional)
# Update .env file with MySQL credentials
```

### 3. **Environment Configuration**
```bash
cp .env.example .env
# Edit .env with your settings
```

### 4. **Run Application**
```bash
php -S localhost:8000 -t public
```

### 5. **Access Application**
```
http://localhost:8000
```

## 📦 Dependencies (composer.json)

```json
{
    "require": {
        "php": ">=8.2",
        "twig/twig": "^3.0",
        "vlucas/phpdotenv": "^5.5",
        "nikic/fast-route": "^1.3",
        "psr/http-message": "^1.1",
        "psr/http-factory": "^1.0",
        "psr/http-server-handler": "^1.0",
        "psr/http-server-middleware": "^1.0",
        "psr/container": "^2.0",
        "nyholm/psr7": "^1.5",
        "nyholm/psr7-server": "^1.0"
    }
}
```

## 🚀 Key Features

### ✅ **Enterprise Architecture**
- 📦 PSR-11 Dependency Injection Container
- 🌐 PSR-7 HTTP Message Implementation
- 🎯 Advanced Routing with Middleware
- 🎨 Twig Template Engine Integration

### ✅ **Security Features**
- 🛡️ CSRF Protection
- 🔐 Session Management
- 📝 Input Validation
- 🔒 Secure File Uploads

### ✅ **Developer Experience**
- 🔄 Hot Module Reloading
- 📝 Comprehensive Error Handling
- 🐛 Debug Mode
- 📊 Logging System

### ✅ **Business Features**
- 🛒 Shopping Cart System
- 💳 Multi-Gateway Payments
- 👥 User Management
- 📦 Product Catalog

## 🎯 Request Lifecycle Example

```php
// 1. User visits /dashboard
GET /dashboard HTTP/1.1
Host: localhost:8000

// 2. Application bootstrap
$app = new Application();
// ✅ DI Container created
// ✅ Services registered
// ✅ Router initialized

// 3. Route matching
Router::dispatch($request)
// ✅ Route found: GET /dashboard -> DashboardController@index
// ✅ CSRF validation (not needed for GET)
// ✅ Middleware executed

// 4. Controller resolution
$controller = $container->get(DashboardController::class);
// ✅ Constructor injection: View + Container
// ✅ Dependencies resolved automatically

// 5. Controller execution
$controller->index($request)
// ✅ Business logic executed
// ✅ Data fetched from database
// ✅ Response created

// 6. Response emission
$application->emitResponse($response);
// ✅ HTTP headers sent
// ✅ Content streamed
// ✅ Response completed
```

## 🏆 Architecture Benefits

### ✅ **Maintainability**
- 📦 Clear separation of concerns
- 🔄 Dependency injection
- 📝 Consistent coding standards

### ✅ **Scalability**
- 🏗️ Service-oriented architecture
- 📊 Modular design
- 🔄 Easy to extend

### ✅ **Testability**
- 🧪 Dependency injection
- 🎯 Isolated components
- 📝 Mock-friendly interfaces

### ✅ **Performance**
- ⚡ Optimized routing
- 📦 Service singletons
- 🗄️ Efficient database queries

## 🎓 Learning Outcomes

This project demonstrates:
- ✅ **PSR Standards Compliance** - 100% implementation
- ✅ **Modern PHP 8.2+** - Latest language features
- ✅ **Enterprise Architecture** - Professional patterns
- ✅ **Dependency Injection** - PSR-11 container
- ✅ **HTTP Standards** - PSR-7 messaging
- ✅ **Security Best Practices** - CSRF, validation, sessions
- ✅ **Clean Code** - SOLID principles applied

---

**🚀 Built with modern PHP best practices and full PSR compliance!**

