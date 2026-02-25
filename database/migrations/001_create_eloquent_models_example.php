<?php

/**
 * Example Migration for Eloquent Models
 * 
 * This file demonstrates how to create migrations using Eloquent
 * You can run this manually or integrate it into your migration system
 */

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Builder;

// Initialize Eloquent (this is done automatically in Application.php)
$capsule = new Capsule;
$capsule->addConnection([
    'driver'    => 'sqlite',
    'database'  => __DIR__ . '/../database.sqlite',
    'prefix'    => '',
]);
$capsule->setAsGlobal();

// Get schema builder
$schema = $capsule->schema();

// Example: Create users table
if (!$schema->hasTable('users')) {
    $schema->create('users', function (Blueprint $table) {
        $table->id();
        $table->string('username')->unique();
        $table->string('email')->unique();
        $table->string('password');
        $table->timestamps();
    });
    echo "✅ Users table created\n";
} else {
    echo "ℹ️ Users table already exists\n";
}

// Example: Create products table
if (!$schema->hasTable('products')) {
    $schema->create('products', function (Blueprint $table) {
        $table->id('product_id');
        $table->string('title');
        $table->decimal('price', 10, 2);
        $table->text('description')->nullable();
        $table->foreignId('user_id')->constrained('users');
        $table->string('image_path')->nullable();
        $table->string('file_path')->nullable();
        $table->timestamps();
    });
    echo "✅ Products table created\n";
} else {
    echo "ℹ️ Products table already exists\n";
}

echo "\n🎉 Migration completed!\n";
echo "\n📋 Available Eloquent Features:\n";
echo "  • Relationships (hasMany, belongsTo)\n";
echo "  • Mass assignment with fillable\n";
echo "  • Hidden attributes for security\n";
echo "  • Automatic timestamps\n";
echo "  • Query builder methods\n";
echo "  • Collections with powerful methods\n";
