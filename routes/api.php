<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\AuthController;

Route::post('/users', [UserController::class, 'store'])->name('users.store');
Route::post('/tokens', [AuthController::class, 'login'])->name('tokens.create');

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum')->name('user.current');

Route::get('/test', function () {
    return response()->json([
        'message' => 'API is working!',
        'status' => 'success',
        'timestamp' => now()->toDateTimeString()
    ]);
})->name('api.test');