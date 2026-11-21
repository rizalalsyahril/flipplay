<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\MediaController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\UserMediaController;
use Illuminate\Support\Facades\Route;

Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);
Route::get('/users', [UserController::class, 'index'])->name('users.index');
Route::post('/users', [UserController::class, 'store'])->name('users.store');
Route::match(['put', 'patch'], '/users/{user}', [UserController::class, 'update'])->name('users.update');
Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
Route::get('/users/{user}/collection', [UserMediaController::class, 'indexForUser']);
Route::post('/users/{user}/collection/{media}', [UserMediaController::class, 'storeForUser']);
Route::delete('/users/{user}/collection/{media}', [UserMediaController::class, 'destroyForUser']);

Route::get('/media', [MediaController::class, 'index'])->name('media.index');
Route::post('/media', [MediaController::class, 'store'])->name('media.store');
Route::get('/media/{media}/users', [UserMediaController::class, 'users']);
Route::get('/media/{media}', [MediaController::class, 'show'])->name('media.show');
Route::match(['put', 'patch'], '/media/{media}', [MediaController::class, 'update'])->name('media.update');
Route::delete('/media/{media}', [MediaController::class, 'destroy'])->name('media.destroy');

Route::middleware('auth:sanctum')->group(function (): void {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/collection', [UserMediaController::class, 'index']);
    Route::put('/collection/{media}', [UserMediaController::class, 'store']);
    Route::delete('/collection/{media}', [UserMediaController::class, 'destroy']);
});
