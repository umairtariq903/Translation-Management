<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TranslationController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\AuthController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
    Route::apiResource('translations', TranslationController::class);
    Route::apiResource('tags', TagController::class);
    Route::get('translations/{id}', [TranslationController::class, 'show']);
    Route::get('/translations/search', [TranslationController::class, 'search']);
    Route::get('/translations-tags/export', [TranslationController::class, 'export']);
});

