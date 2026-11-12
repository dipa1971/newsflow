<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\OperatorController;
use App\Http\Controllers\SiteController;
use App\Http\Middleware\EnsureOperator;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect('/dashboard'));

// শুধু যারা লগইন করেননি
Route::middleware('guest')->group(function () {
    Route::get('/register', [AuthController::class, 'showRegister']);
    Route::post('/register', [AuthController::class, 'register']);
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');
});

// শুধু যারা লগইন করেছেন
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/dashboard', [DashboardController::class, 'index']);

    Route::resource('sites', SiteController::class);
    Route::post('/sites/{site}/regenerate-secret', [SiteController::class, 'regenerateSecret']);

    // শুধু অপারেটর
    Route::middleware(EnsureOperator::class)->prefix('operator')->group(function () {
        Route::get('/', [OperatorController::class, 'index']);
        Route::post('/sites/{site}/toggle', [OperatorController::class, 'toggle']);
    });
});
