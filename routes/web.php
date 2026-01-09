<?php

declare(strict_types=1);

use App\Http\Controllers\Auth\AdminAuthController;
use App\Http\Controllers\Auth\CustomerAuthController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\AttributeController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ColorController;
use App\Http\Controllers\FabricController;
use App\Http\Controllers\FitController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SizeController;
use App\Http\Controllers\StoreController;
use App\Http\Controllers\TaxClassController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin Authentication Routes
|--------------------------------------------------------------------------
*/
Route::prefix('api/admin')->group(function () {
    Route::post('/login', [AdminAuthController::class, 'login'])->name('admin.login');
    Route::post('/logout', [AdminAuthController::class, 'logout'])->middleware('admin.guard')->name('admin.logout');
    Route::get('/me', [AdminAuthController::class, 'me'])->middleware('admin.guard')->name('admin.me');
});

/*
|--------------------------------------------------------------------------
| Password Reset Routes
|--------------------------------------------------------------------------
*/
Route::prefix('api/admin')->group(function () {
    Route::post('/forgot-password', [PasswordResetController::class, 'forgotPassword'])->name('password.email');
    Route::post('/reset-password', [PasswordResetController::class, 'resetPassword'])->name('password.update');
});

/*
|--------------------------------------------------------------------------
| User Management Routes (Admin Only)
|--------------------------------------------------------------------------
*/
Route::prefix('api/admin')->middleware(['admin.guard', 'role:super_admin,admin'])->group(function () {
    Route::apiResource('users', UserController::class);
});

/*
|--------------------------------------------------------------------------
| Product Management Routes (Admin Only)
|--------------------------------------------------------------------------
*/
Route::prefix('api/admin')->middleware(['admin.guard', 'role:super_admin,admin'])->group(function () {
    // Core product resources
    Route::apiResource('categories', CategoryController::class);
    Route::apiResource('brands', BrandController::class);
    Route::apiResource('attributes', AttributeController::class);
    Route::apiResource('products', ProductController::class);
    
    // Product variants
    Route::post('products/{product}/variants', [ProductController::class, 'storeVariant']);
    Route::put('products/{product}/variants/{variant}', [ProductController::class, 'updateVariant']);
    Route::delete('products/{product}/variants/{variant}', [ProductController::class, 'destroyVariant']);
    
    // Attribute values
    Route::post('attributes/{attribute}/values', [AttributeController::class, 'addValue']);
    Route::delete('attributes/{attribute}/values/{value}', [AttributeController::class, 'removeValue']);
    
    // Variant attributes (Sizes, Colors, Fabrics, Fits)
    Route::apiResource('sizes', SizeController::class);
    Route::apiResource('colors', ColorController::class);
    Route::apiResource('fabrics', FabricController::class);
    Route::apiResource('fits', FitController::class);
    
    // Tax and Store settings
    Route::apiResource('tax-classes', TaxClassController::class);
    Route::apiResource('stores', StoreController::class);
});

/*
|--------------------------------------------------------------------------
| Customer Authentication Routes
|--------------------------------------------------------------------------
*/
Route::prefix('api/customer')->group(function () {
    Route::post('/login', [CustomerAuthController::class, 'login'])->name('customer.login');
    Route::post('/register', [CustomerAuthController::class, 'register'])->name('customer.register');
    Route::post('/logout', [CustomerAuthController::class, 'logout'])->middleware('customer.guard')->name('customer.logout');
    Route::get('/me', [CustomerAuthController::class, 'me'])->middleware('customer.guard')->name('customer.me');
});

/*
|--------------------------------------------------------------------------
| SPA Catch-All Route
|--------------------------------------------------------------------------
*/
Route::get('/{any}', function () {
    return view('welcome');
})->where('any', '.*');
