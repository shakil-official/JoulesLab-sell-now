# SellNow (Assessment Project)

This is a **simplified, imperfect** platform for selling digital products, built for **candidate assessment functionality**.
It contains **intentional flaws, bad practices, and security holes**.

## Project Overview

A platform where:
1. Users register and get a public profile (`/username`).
2. Users can upload products (images + digital files).
3. Buyers can browse, add to cart, and "checkout".

## Setup Instructions

1. **Install Dependencies**:
   ```bash
   composer install
   ```

2. **Database**:
   The project is configured to use SQLite by default.
   Initialize the database:
   ```bash
   sqlite3 database/database.sqlite < database/schema.sql
   ```
   *Note: If you switch to MySQL, update `src/Config/Database.php`.*

3. **Run Server**:
   Use PHP built-in server:
   ```bash
   php -S localhost:8000 -t public
   ```

4. **Access**:
   http://localhost:8000


## Directory Structure

```
SellNow/
├── app/
│   └── Core/
│       ├── Config/
│       ├── Contracts/
│       ├── Controller/
│       ├── Database/
│       ├── Route/
│       ├── Services/
│       └── View/
├── src/
│   ├── Contracts/
│   │   └── PaymentGatewayInterface.php
│   ├── Controllers/
│   │   ├── AuthController.php
│   │   ├── CartController.php
│   │   ├── CheckoutController.php
│   │   ├── DashboardController.php
│   │   ├── ProductController.php
│   │   ├── PublicController.php
│   │   └── TestController.php
│   ├── Middlewares/
│   │   └── AuthMiddleware.php
│   ├── Models/
│   │   ├── Product.php
│   │   └── User.php
│   ├── Routes/
│   │   └── web.php
│   └── Services/
│       ├── Cart/
│       ├── Payments/
│       │   ├── Gateways/
│       │   │   ├── PayPalGateway.php
│       │   │   ├── RazorpayGateway.php
│       │   │   └── StripeGateway.php
│       │   └── PaymentGatewayFactory.php
│       └── Product/
├── public/
│   ├── index.php
│   └── uploads/
├── templates/
│   ├── auth/
│   ├── dashboard/
│   ├── products/
│   └── ...
├── database/
│   ├── database.sqlite
│   └── schema.sql
├── storage/
│   └── logs/
│       └── transactions.log
├── composer.json
├── composer.lock
└── README.md
```

## Application Lifecycle

The SellNow application follows a **custom MVC architecture** with the following lifecycle:

### 1. Entry Point (`public/index.php`)
- **Bootstrap**: Loads Composer autoloader and starts session
- **Twig Setup**: Configures template engine with global session data and CSRF helper
- **Database Connection**: Initializes database connection via Model base class
- **Router Initialization**: Creates Router instance and loads application routes

### 2. Core Project (`app/Core/`)
The `app/Core/` directory contains the **Project foundation**:

#### **Controller** (`app/Core/Controller/Controller.php`)
- Base controller class with `render()` and `json()` methods
- Handles flash messages and template rendering
- All application controllers extend this base class

#### **Routing System** (`app/Core/Route/`)
- **Router.php**: Main request dispatcher that matches URLs to controllers
- **Route.php**: Fluent interface for route registration
- **Request.php**: HTTP request wrapper with method/URI detection
- Handles CSRF validation for POST requests and middleware execution

#### **Database Layer** (`app/Core/Database/`)
- **Database.php**: Singleton database connection manager
- **Model.php**: Base model class with ORM-like functionality

#### **View System** (`app/Core/View/`)
- **View.php**: Template rendering wrapper around Twig
- Integrates with controller base class for consistent rendering

#### **Configuration** (`app/Core/Config/`)
- **Helper.php**: Utility functions (redirects, password hashing, flash messages)
- **Csrf.php**: CSRF token generation and validation

### 3. Application Code (`src/`)
The `src/` directory contains **business logic**:

#### **Controllers** (`src/Controllers/`)
- Handle HTTP requests and coordinate business logic
- Extend `App\Core\Controller\Controller` for rendering capabilities
- Examples: `AuthController`, `ProductController`, `CartController`

#### **Models** (`src/Models/`)
- Represent database entities (User, Product)
- Extend `App\Core\Database\Model` for database operations

#### **Services** (`src/Services/`)
- Business logic separation (Cart, Payments, Product services)
- Payment gateway implementations (Stripe, PayPal, Razorpay)

#### **Routes** (`src/Routes/web.php`)
- Defines all application URL patterns
- Maps HTTP methods + URLs to controller actions
- Applies middleware for authentication/authorization

### 4. Request Flow
1. **HTTP Request** → `public/index.php`
2. **Router** matches URL pattern from `src/Routes/web.php`
3. **Middleware** execution (authentication, CSRF validation)
4. **Controller** instantiation and method call
5. **Business Logic** in services/models
6. **Response** via Twig templates or JSON

### 5. Key Features
- **Custom MVC**: Lightweight Project built from scratch
- **CSRF Protection**: Built-in token validation for forms
- **Middleware Pipeline**: Authentication and request filtering
- **Flash Messaging**: Session-based notifications
- **Payment Integration**: Multiple gateway support
- **File Uploads**: Product image and digital file handling

This architecture provides a **separation of concerns** with the core Project (`app/`) handling infrastructure and the application code (`src/`) focusing on business functionality.

## Architecture Diagram

```
┌─────────────────────────────────────────────────────────────────┐
│                        HTTP REQUEST                              │
└─────────────────────┬───────────────────────────────────────────┘
                      │
                      ▼
┌─────────────────────────────────────────────────────────────────┐
│                  public/index.php                               │
│  ┌─────────────┐ ┌─────────────┐ ┌─────────────┐ ┌─────────────┐│
│  │   Session   │ │    Twig     │ │  Database   │ │   Router    ││
│  │   Start     │ │   Setup     │ │ Connection  │ │ Initialization│
│  └─────────────┘ └─────────────┘ └─────────────┘ └─────────────┘│
└─────────────────────┬───────────────────────────────────────────┘
                      │
                      ▼
┌─────────────────────────────────────────────────────────────────┐
│                 app/Core/Route/Router.php                        │
│  ┌─────────────┐ ┌─────────────┐ ┌─────────────┐ ┌─────────────┐│
│  │    URL      │ │   CSRF      │ │  Middleware │ │  Controller ││
│  │   Matching  │ │ Validation  │ │ Execution   │ │ Invocation  ││
│  └─────────────┘ └─────────────┘ └─────────────┘ └─────────────┘│
└─────────────────────┬───────────────────────────────────────────┘
                      │
                      ▼
┌─────────────────────────────────────────────────────────────────┐
│                   src/Controllers/                              │
│  ┌─────────────┐ ┌─────────────┐ ┌─────────────┐ ┌─────────────┐│
│  │   Request   │ │   Models    │ │  Services   │ │   Response  ││
│  │  Handling   │ │ Interaction │ │  Logic      │ │ Generation  ││
│  └─────────────┘ └─────────────┘ └─────────────┘ └─────────────┘│
└─────────────────────┬───────────────────────────────────────────┘
                      │
                      ▼
┌─────────────────────────────────────────────────────────────────┐
│                    RESPONSE                                      │
│  ┌─────────────┐ ┌─────────────┐ ┌─────────────┐ ┌─────────────┐│
│  │   Twig      │ │    JSON     │ │   Redirect  │ │     HTTP    ││
│  │  Templates  │ │   Response  │ │    Header    │ │   Status    ││
│  └─────────────┘ └─────────────┘ └─────────────┘ └─────────────┘│
└─────────────────────────────────────────────────────────────────┘
```

## Directory Structure Flow

```
SellNow Project
├── 📁 app/Core/                    # Framework Foundation
│   ├── 📁 Config/                  # Configuration & Helpers
│   ├── 📁 Controller/              # Base Controller Class
│   ├── 📁 Database/                # Database Connection & Model Base
│   ├── 📁 Route/                   # Router, Request, Route Classes
│   ├── 📁 Services/                # Core Services
│   └── 📁 View/                    # Template Rendering
│
├── 📁 src/                         # Application Logic
│   ├── 📁 Controllers/             # HTTP Request Handlers
│   ├── 📁 Models/                  # Database Entities
│   ├── 📁 Services/                # Business Logic
│   ├── 📁 Middlewares/             # Request Filters
│   └── 📁 Routes/                  # URL Definitions
│
├── 📁 public/                      # Web Root
│   ├── 📄 index.php               # Entry Point
│   └── 📁 uploads/                # File Storage
│
├── 📁 templates/                   # Twig Views
├── 📁 database/                    # Database Files
└── 📁 storage/                     # Logs & Storage
```

## Data Flow Diagram

```
User Request
     │
     ▼
┌─────────────┐
│   Browser   │
└─────┬───────┘
      │ HTTP Request
      ▼
┌─────────────┐
│public/index│
│    .php     │
└─────┬───────┘
      │ Initialize Router
      ▼
┌─────────────┐
│   Router    │◄─── Routes from src/Routes/web.php
└─────┬───────┘
      │ Match Route
      ▼
┌─────────────┐
│ Middleware  │ (Auth, CSRF)
└─────┬───────┘
      │ Validate
      ▼
┌─────────────┐
│ Controller  │ (src/Controllers/)
└─────┬───────┘
      │ Business Logic
      ▼
┌─────────────┐
│  Models &   │ (src/Models/, src/Services/)
│  Services   │
└─────┬───────┘
      │ Data Processing
      ▼
┌─────────────┐
│   Response  │ (Twig/JSON/Redirect)
└─────┬───────┘
      │ HTTP Response
      ▼
┌─────────────┐
│   Browser   │
└─────────────┘
```

