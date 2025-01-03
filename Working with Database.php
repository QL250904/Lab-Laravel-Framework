
<?php

// Laravel Database Lab Implementation

// 1. Setup Laravel Project
// Run these commands in your terminal:
// composer create-project laravel/laravel CoffeeApp
// cd CoffeeApp
// 
// Configure .env for database connection
//
// DB_CONNECTION=mysql
// DB_HOST=127.0.0.1
// DB_PORT=3306
// DB_DATABASE=coffee_app
// DB_USERNAME=root
// DB_PASSWORD=

// 2. Migrations

// a. Create categories table migration
// Run: php artisan make:migration create_categories_table

// File: database/migrations/<timestamp>_create_categories_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};

// b. Create products table migration
// Run: php artisan make:migration create_products_table

// File: database/migrations/<timestamp>_create_products_table.php
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description');
            $table->double('price');
            $table->string('currency');
            $table->string('display_image_url');
            $table->foreignId('category_id')->nullable()->constrained('categories')->cascadeOnUpdate()->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};

// Run migrations: php artisan migrate

// 3. Models

// a. Create models
// Run: php artisan make:model Category
// Run: php artisan make:model Product

// File: app/Models/Category.php


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}

// File: app/Models/Product.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    public const DEFAULT_CURRENCY = 'VNĐ';
    public const DEFAULT_IMAGE = 'https://images.unsplash.com/photo-1509042239860-f550ce710b93';

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function getFormattedPriceAttribute()
    {
        return number_format($this->price) . ' ' . $this->currency;
    }

    public function getFormattedTotalAmount($quantity = 1)
    {
        return number_format($this->price * $quantity) . ' ' . $this->currency;
    }
}

// 4. Factories and Seeders

// a. Create factories
// Run: php artisan make:factory CategoryFactory
// Run: php artisan make:factory ProductFactory

// File: database/factories/CategoryFactory.php
namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class CategoryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->sentence(),
            'description' => fake()->paragraph(),
        ];
    }
}

// File: database/factories/ProductFactory.php
namespace Database\Factories;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->sentence(),
            'description' => fake()->paragraph(),
            'price' => fake()->numerify('#####'),
            'currency' => Product::DEFAULT_CURRENCY,
            'display_image_url' => Product::DEFAULT_IMAGE,
            'category_id' => Category::factory(),
        ];
    }
}

// b. Create seeders
// Run: php artisan make:seeder CategorySeeder
// Run: php artisan make:seeder ProductSeeder

// File: database/seeders/CategorySeeder.php
namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        Category::factory()->count(5)->create();
    }
}

// File: database/seeders/ProductSeeder.php
namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        Product::factory()->count(20)->create();
    }
}

// Register seeders in DatabaseSeeder
// File: database/seeders/DatabaseSeeder.php
namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            CategorySeeder::class,
            ProductSeeder::class,
        ]);
    }
}

// Run seeders: php artisan db:seed

// 5. Query Builder

// a. Create Artisan Command
// Run: php artisan make:command QueryBuilderPlayground

// File: app/Console/Commands/QueryBuilderPlayground.php
namespace App\Console\Commands;

use App\Models\Category;
use Illuminate\Console\Command;

class QueryBuilderPlayground extends Command
{
    protected $signature = 'app:query-builder-playground';
    protected $description = 'Query Builder Playground';

    public function handle()
    {
        $categories = Category::query()->with('products')->get();

        foreach ($categories as $category) {
            dump('Category name: '.$category->name);
            dump('Total products: '.count($category->products));

            foreach ($category->products as $product) {
                dump('--- Product name: '.$product->name);
            }

            dump('--------------------------------------------');
        }
    }
}

// Run command: php artisan app:query-builder-playground

// b. Using Tinker
// Run: php artisan tinker
// Use the following commands in Tinker
// use App\Models\Category;
// $categories = Category::query()->with('products')->get();
// dump($categories);
