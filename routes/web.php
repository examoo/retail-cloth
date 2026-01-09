<?php

declare(strict_types=1);

use App\Http\Controllers\Auth\AdminAuthController;
use App\Http\Controllers\Auth\CustomerAuthController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\AttributeController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\CategoryController;
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
    Route::apiResource('categories', CategoryController::class);
    Route::apiResource('brands', BrandController::class);
    Route::apiResource('attributes', AttributeController::class);
    
    // Attribute value management
    Route::post('attributes/{attribute}/values', [AttributeController::class, 'addValue']);
    Route::delete('attributes/{attribute}/values/{value}', [AttributeController::class, 'removeValue']);
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

