<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\UserProfileController;
use App\Http\Middleware\SecurityHeadersMiddleware;
use App\Http\Middleware\IsAuthorizedMiddleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::middleware(SecurityHeadersMiddleware::class)->group(function () {
    Route::post('v1/user/signup', [RegisterController::class, 'signup']);
    Route::post('v1/user/login', [RegisterController::class, 'login']);
    Route::prefix('v1/auth')->group(function () {
        Route::get('google/redirect', [AuthController::class, 'googleRedirect']);
        Route::get('google/callback', [AuthController::class, 'googleCallback']);
        Route::post('forgot-password', [AuthController::class, 'forgotPassword']);
        Route::post('reset-password', [AuthController::class, 'resetPassword']);
    });

    Route::middleware(IsAuthorizedMiddleware::class)->group(function () {
        Route::prefix('v1/user')->group(function () {
            Route::prefix('profile')->group(function () {
                Route::get('/', [UserProfileController::class, 'profileInfo']);
                Route::put('/', [UserProfileController::class, 'updateProfileInfo']);
                Route::put('/password', [UserProfileController::class, 'updatePassword']);
            });
        });
        Route::delete('v1/user/logout', [UserProfileController::class, 'logout']);
    });
});
