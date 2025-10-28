<?php



use App\Http\Controllers\Admin\SettingsController;
use Illuminate\Support\Facades\Route;

// Settings
Route::prefix('v1/settings')->group(function () {
    Route::get('/', [SettingsController::class, 'index']);
    Route::patch('/', [SettingsController::class, 'update']);
});

