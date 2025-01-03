
<?php

// Laravel REST API Lab Implementation

// 1. Setup API Routes
// File: routes/api.php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\CategoryController;
use App\Http\Controllers\API\ProductController;

Route::prefix('v1')->group(function () {
    Route::apiResource('categories', CategoryController::class);
    Route::apiResource('products', ProductController::class);
});

// 2. Categories API Controller
// Run: php artisan make:controller API/CategoryController --api --model=Category

// File: app/Http/Controllers/API/CategoryController.php


use App\Models\Category;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\Database\Query\Builder;

class CategoryController extends Controller
{
    public function index()
    {
        $query = Category::query()
            ->when(request('search'), function(Builder $query, $search) {
                return $query->where('name', 'like', '%'.$search.'%');
            });

        return $query->simplePaginate();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|unique:categories|max:255',
            'description' => 'required|max:255',
        ]);

        return Category::create($validated);
    }

    public function show(Category $category)
    {
        return $category;
    }

    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => 'nullable|unique:categories|max:255',
            'description' => 'nullable|max:255',
        ]);

        $category->update($validated);
        return $category;
    }

    public function destroy(Category $category)
    {
        $category->delete();
        return $category;
    }
}

// 3. Products API Controller
// Run: php artisan make:controller API/ProductController --api --model=Product

// File: app/Http/Controllers/API/ProductController.php
namespace App\Http\Controllers\API;

use App\Models\Product;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\Database\Query\Builder;

class ProductController extends Controller
{
    public function index()
    {
        $query = Product::query()
            ->when(request('with'), function(Builder $query, $with) {
                $query->with(explode(',', $with));
            })
            ->when(request('search'), function(Builder $query, $search) {
                return $query->where('name', 'like', '%'.$search.'%');
            });

        return $query->simplePaginate();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|unique:products|max:255',
            'description' => 'required|max:255',
            'price' => 'required|numeric',
            'currency' => 'required|string',
            'display_image_url' => 'required|url',
            'category_id' => 'required|exists:categories,id',
        ]);

        return Product::create($validated);
    }

    public function show(Product $product)
    {
        $product->loadMissing(explode(',', request('with', '')));
        return $product;
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => 'nullable|unique:products|max:255',
            'description' => 'nullable|max:255',
            'price' => 'nullable|numeric',
            'currency' => 'nullable|string',
            'display_image_url' => 'nullable|url',
            'category_id' => 'nullable|exists:categories,id',
        ]);

        $product->update($validated);
        return $product;
    }

    public function destroy(Product $product)
    {
        $product->delete();
        return $product;
    }
}

// 4. Postman Testing
// Use Postman to test the endpoints with CRUD operations for categories and products

