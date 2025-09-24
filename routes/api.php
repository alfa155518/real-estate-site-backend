<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\RegisterController;
use App\Http\Middleware\SecurityHeadersMiddleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::middleware(SecurityHeadersMiddleware::class)->group(function () {
    Route::post('v1/user/signup', [RegisterController::class, 'signup']);
    Route::post('v1/user/login', [RegisterController::class, 'login']);
    Route::get('v1/auth/google/redirect', [AuthController::class, 'googleRedirect']);
    Route::get('v1/auth/google/callback', [AuthController::class, 'googleCallback']);
    Route::post('v1/auth/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('v1/auth/reset-password', [AuthController::class, 'resetPassword']);
});
