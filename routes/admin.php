<?php



use App\Http\Controllers\Admin\ReviewsController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\UsersController;
use App\Http\Middleware\AdminSecurityHeadersMiddleware;
use Illuminate\Support\Facades\Route;

Route::middleware(AdminSecurityHeadersMiddleware::class)->group(function () {


    // Manage Settings
    Route::prefix('v1/settings')->group(function () {
        Route::get('/', [SettingsController::class, 'index']);
        Route::patch('/', [SettingsController::class, 'update']);
    });

    // Manage Reviews
    Route::prefix('v1/reviews')->group(function () {
        Route::get('/', [ReviewsController::class, 'index']);
        Route::delete('/{id}', [ReviewsController::class, 'delete']);
    });

    // Manage Users
    Route::prefix('v1/users')->group(function () {
        Route::get('/', [UsersController::class, 'index']);
        Route::patch('/{id}', [UsersController::class, 'update']);
        Route::delete('/{id}', [UsersController::class, 'delete']);
    });
});