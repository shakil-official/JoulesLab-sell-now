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

