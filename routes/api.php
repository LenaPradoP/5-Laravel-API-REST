<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\AuthController;

Route::post('/users', [UserController::class, 'store'])->name('users.store');
Route::post('/tokens', [AuthController::class, 'login'])->name('tokens.create');

Route::middleware('auth:api')->group(function () {
    Route::delete('/tokens', [AuthController::class, 'logout'])->name('tokens.destroy');
    Route::get('/users/{id?}', [UserController::class, 'show'])->name('users.show');
    Route::put('/users/{id?}', [UserController::class, 'update'])->name('users.update');
});

Route::get('/test', function () {
    return response()->json([
        'message' => 'API is working!',
        'status' => 'success',
        'timestamp' => now()->toDateTimeString()
    ]);
})->name('api.test');