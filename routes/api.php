<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\{UserController, AuthController, DeckController, SpreadController};

Route::post('/users', [UserController::class, 'store'])->name('users.store');
Route::post('/tokens', [AuthController::class, 'login'])->name('tokens.create');

Route::middleware('auth:api')->group(function () {
    Route::delete('/tokens', [AuthController::class, 'logout'])->name('tokens.destroy');
    Route::get('/users/{id?}', [UserController::class, 'show'])->name('users.show');
    Route::put('/users/{id?}', [UserController::class, 'update'])->name('users.update');
    Route::delete('/users', [UserController::class, 'destroy'])->name('users.destroy');
    Route::get('/decks', [DeckController::class, 'index'])->name('decks.index');
    Route::put('/decks', [DeckController::class, 'update']);
    Route::get('/spreads', [SpreadController::class, 'index'])->name('spreads.index');
    Route::post('/spreads', [SpreadController::class, 'store'])->name('spreads.store');
    Route::get('/spreads/{id}', [SpreadController::class, 'show'])->name('spreads.show');
});

Route::get('/test', function () {
    return response()->json([
        'message' => 'API is working!',
        'status' => 'success',
        'timestamp' => now()->toDateTimeString()
    ]);
})->name('api.test');