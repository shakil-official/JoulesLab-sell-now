# Enhanced Routing System Documentation

## Overview
The routing system has been completely rewritten to provide a modern, fast, and feature-rich routing experience similar to Laravel Express.

## Key Features

### 1. HTTP Methods
All HTTP methods are now supported:
- `GET`, `POST`, `PUT`, `PATCH`, `DELETE`, `OPTIONS`
- `ANY` - matches all methods
- `MATCH(['GET', 'POST'])` - matches specific methods

### 2. Route Groups & Prefixes
```php
Route::prefix('/admin', function () {
    // All routes here will have /admin prefix
});

Route::group(['middleware' => 'auth', 'prefix' => '/api'], function () {
    // Combined prefix and middleware
});
```

### 3. Named Routes
```php
Route::get()->url('/users/{id}')->controller(UserController::class)
    ->name('users.show')
    ->method('show');

// Generate URL: route('users.show', ['id' => 1])
```

### 4. Parameter Constraints
```php
Route::get()->url('/users/{id}')->controller(UserController::class)
    ->where('id', '[0-9]+')  // Only numeric IDs
    ->method('show');

Route::get()->url('/posts/{slug}')->controller(PostController::class)
    ->where('slug', '[a-z0-9-]+')  // Only alphanumeric with dashes
    ->method('show');
```

### 5. Resource Routes
```php
// Full resource (7 routes)
Route::resource('products', ProductController::class);

// API resource (5 routes, no create/edit views)
Route::apiResource('products', ProductController::class);
```

### 6. Special Route Types
```php
// Redirect routes
Route::redirect('/old-url', '/new-url', 301);

// View routes (direct to template)
Route::view('/about', 'about', ['title' => 'About Us']);
```

### 7. Middleware Groups
```php
// Predefined groups: web, api, auth, guest
Route::group(['middleware' => 'auth'], function () {
    // Protected routes
});

// Custom middleware groups
$router->addMiddlewareGroup('admin', [AuthMiddleware::class, AdminMiddleware::class]);
```

### 8. Route Caching
```php
// Automatically enabled in index.php for production
$router->enableCache();
$router->clearCache(); // Clear cache when routes change
```

### 9. Enhanced Request Class
The Request class now includes:
- File upload handling with `UploadedFile` objects
- JSON request parsing
- Header access (`$request->getHeader('Content-Type')`)
- IP address detection
- AJAX/PJAX detection
- Built-in validation
- Method spoofing for HTML forms

### 10. Better Error Handling
- Custom 404/405 error controllers
- Proper HTTP status codes
- Detailed error messages for debugging

## Migration Guide

### Old Syntax
```php
Route::get()
    ->url('/products/add')
    ->controller(ProductController::class)
    ->middleware(AuthMiddleware::class)
    ->method('index');
```

### New Syntax
```php
Route::group(['middleware' => 'auth'], function () {
    Route::prefix('/products', function () {
        Route::get()->url('/add')->controller(ProductController::class)
            ->name('products.create')
            ->method('index');
    });
});
```

## Performance Improvements

1. **Route Caching**: Routes are compiled and cached for faster lookup
2. **Optimized Dispatcher**: Uses FastRoute with optimizations
3. **Lazy Loading**: Middleware and controllers are only loaded when needed
4. **Parameter Constraints**: Regex patterns are compiled once

## Security Features

1. **CSRF Protection**: Built-in CSRF validation for POST requests
2. **Method Spoofing Protection**: Secure handling of _method parameter
3. **Input Validation**: Built-in validation rules
4. **File Upload Security**: Proper file type and size validation

## API Examples

### Basic CRUD with Resource Routes
```php
Route::resource('users', UserController::class);
// Creates: index, create, store, show, edit, update, destroy
```

### API Endpoints
```php
Route::prefix('/api')->group(['middleware' => 'api'], function () {
    Route::apiResource('products', ProductController::class);
    Route::get()->url('/search')->controller(SearchController::class)
        ->name('api.search')
        ->method('search');
});
```

### Parameter Constraints
```php
Route::get()->url('/users/{id}/posts/{post_id}')->controller(UserPostController::class)
    ->where('id', '[0-9]+')
    ->where('post_id', '[0-9]+')
    ->name('users.posts.show')
    ->method('show');
```

This enhanced routing system provides all modern routing features while maintaining backward compatibility with existing controllers and methods.
