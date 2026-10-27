<?php

use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\TokenController;
use Illuminate\Support\Facades\Route;

Route::post('/tokens', [TokenController::class, 'store'])->middleware('throttle:login');

Route::middleware(['auth:sanctum', 'admin', 'throttle:api'])->group(function () {
    Route::delete('/tokens/current', [TokenController::class, 'destroy']);
    Route::delete('/products/bulk', [ProductController::class, 'bulkDestroy']);
    Route::apiResource('products', ProductController::class)->only(['index', 'store', 'show', 'destroy']);
});
