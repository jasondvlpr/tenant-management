<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\TenantController;
use App\Http\Controllers\Api\NewsApiController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('central/v1')->group(function () {
    Route::get('/tenants/{id}', [TenantController::class, 'show']);
});

Route::get('/news', [NewsApiController::class, 'index']);
