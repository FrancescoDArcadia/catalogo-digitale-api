<?php

use App\Http\Controllers\Api\AuthorController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\WorkController;
use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Auth;

/* Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
 */
/* Route::get('/ping', function () {
    return response()->json([
        'message' => 'pong',
        'timestamp' => now(),
    ]);
});
 */
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:login');
    
Route::middleware('auth:sanctum')->group( function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/logout-all', [AuthController::class, 'logoutAll']);
    Route::apiResource('categories', CategoryController::class);
    Route::apiResource('authors', AuthorController::class);
    Route::apiResource('works', WorkController::class);
});