<?php

use App\Http\Controllers\ConnectorController;
use App\Http\Middleware\VerifySiteSignature;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')
    ->middleware(['throttle:120,1', VerifySiteSignature::class])
    ->group(function () {
        Route::get('/ping', [ConnectorController::class, 'ping']);
        Route::post('/articles', [ConnectorController::class, 'store']);
    });
