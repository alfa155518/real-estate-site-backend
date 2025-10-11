<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\PropertiesController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\ReviewsController;
use App\Http\Controllers\UserProfileController;
use App\Http\Middleware\SecurityHeadersMiddleware;
use App\Http\Middleware\IsAuthorizedMiddleware;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| All routes are prefixed with /api and protected by SecurityHeadersMiddleware
|
*/

Route::middleware(SecurityHeadersMiddleware::class)->group(function () {

    // ============================================
    // Public Routes (No Authentication Required)
    // ============================================

    // User Authentication
    Route::prefix('v1/user')->group(function () {
        Route::post('signup', [RegisterController::class, 'signup']);
        Route::post('login', [RegisterController::class, 'login']);
    });

    // OAuth & Password Reset
    Route::prefix('v1/auth')->group(function () {
        Route::get('google/redirect', [AuthController::class, 'googleRedirect']);
        Route::get('google/callback', [AuthController::class, 'googleCallback']);
        Route::post('forgot-password', [AuthController::class, 'forgotPassword']);
        Route::post('reset-password', [AuthController::class, 'resetPassword']);
    });

    // Public Reviews (Read Only)
    Route::prefix('v1/reviews')->group(function () {
        Route::get('/', [ReviewsController::class, 'allReviews']);
        Route::get('{propertyId}', [ReviewsController::class, 'reviewsByProperty']);
    });

    //Public Properties
    Route::prefix('v1/properties')->group(function () {
        Route::get('/', [PropertiesController::class, 'index']);
        Route::get('/property/{slug}', [PropertiesController::class, 'singleProperty']);
        Route::get('/filter', [PropertiesController::class, 'filterByParams']);
    });

    // ============================================
    // Protected Routes (Authentication Required)
    // ============================================

    Route::middleware(IsAuthorizedMiddleware::class)->group(function () {

        // User Profile Management
        Route::prefix('v1/user')->group(function () {
            Route::prefix('profile')->group(function () {
                Route::get('/', [UserProfileController::class, 'profileInfo']);
                Route::patch('/', [UserProfileController::class, 'updateProfileInfo']);
                Route::patch('password', [UserProfileController::class, 'updatePassword']);
            });
            Route::delete('logout', [UserProfileController::class, 'logout']);
        });

        // Reviews Management (Create, Like)
        Route::prefix('v1/reviews')->group(function () {
            Route::post('/', [ReviewsController::class, 'createReview']);
            Route::patch('{id}/toggle-like', [ReviewsController::class, 'toggleLike']);
        });
    });
});
