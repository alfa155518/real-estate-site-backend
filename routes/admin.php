<?php



use App\Http\Controllers\Admin\ReviewsController;
use App\Http\Controllers\Admin\SettingsController;
use Illuminate\Support\Facades\Route;

// Settings
Route::prefix('v1/settings')->group(function () {
    Route::get('/', [SettingsController::class, 'index']);
    Route::patch('/', [SettingsController::class, 'update']);
});
// Reviews
Route::prefix('v1/reviews')->group(function () {
    Route::get('/', [ReviewsController::class, 'index']);
    Route::delete('/{id}', [ReviewsController::class, 'delete']);
});

