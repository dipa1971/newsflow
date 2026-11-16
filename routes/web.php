<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FacebookController;
use App\Http\Controllers\OperatorController;
use App\Http\Controllers\PostController;
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

    Route::resource('sites', SiteController::class)->only(['index', 'create', 'store', 'show', 'destroy']);

    // একটা সাইটের সব কাজের পাতা
    Route::prefix('sites/{site}')->group(function () {
        Route::get('/cards', [SiteController::class, 'cards']);
        Route::get('/log', [SiteController::class, 'log']);

        Route::get('/connections', [SiteController::class, 'connections']);
        Route::put('/connections', [SiteController::class, 'updateConnections']);

        Route::get('/design', [SiteController::class, 'design']);
        Route::put('/design', [SiteController::class, 'updateDesign']);

        Route::get('/settings', [SiteController::class, 'settings']);
        Route::put('/settings', [SiteController::class, 'updateSettings']);

        Route::post('/regenerate-secret', [SiteController::class, 'regenerateSecret']);
        Route::post('/check-now', [SiteController::class, 'checkNow']);
        Route::post('/test-latest', [SiteController::class, 'testLatest']);

        // ফেসবুক সংযোগ
        Route::get('/facebook/connect', [FacebookController::class, 'connect']);
        Route::get('/facebook/pages', [FacebookController::class, 'pages']);
        Route::post('/facebook/choose', [FacebookController::class, 'choose']);
        Route::post('/facebook/disconnect', [FacebookController::class, 'disconnect']);
    });

    // ফেসবুক যেখানে ফেরত পাঠায় (সাইটের নম্বর এখানে থাকে না, সেশন থেকে নেওয়া হয়)
    Route::get('/facebook/callback', [FacebookController::class, 'callback']);

    // ব্যর্থ পোস্ট আবার পাঠানো
    Route::post('/posts/{post}/retry', [PostController::class, 'retry']);

    // শুধু অপারেটর
    Route::middleware(EnsureOperator::class)->prefix('operator')->group(function () {
        Route::get('/', [OperatorController::class, 'index']);
        Route::post('/sites/{site}/toggle', [OperatorController::class, 'toggle']);
    });
});
