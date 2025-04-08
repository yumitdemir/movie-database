<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\MovieApiController;
use App\Http\Controllers\Api\CommentApiController;
use App\Http\Controllers\Api\RatingApiController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Movie API Routes
Route::prefix('movies')->group(function () {
    Route::get('/', [MovieApiController::class, 'index']);
    Route::post('/', [MovieApiController::class, 'store']);
    Route::get('/search', [MovieApiController::class, 'search']);
    Route::get('/{id}', [MovieApiController::class, 'show']);
    Route::put('/{id}', [MovieApiController::class, 'update']);
    Route::delete('/{id}', [MovieApiController::class, 'destroy']);
    Route::get('/{id}/statistics', [MovieApiController::class, 'statistics']);
});

// Comment API Routes
Route::prefix('comments')->group(function () {
    Route::get('/', [CommentApiController::class, 'index']);
    Route::post('/', [CommentApiController::class, 'store']);
    Route::get('/{id}', [CommentApiController::class, 'show']);
    Route::put('/{id}', [CommentApiController::class, 'update']);
    Route::delete('/{id}', [CommentApiController::class, 'destroy']);
});

// Rating API Routes
Route::prefix('ratings')->group(function () {
    Route::post('/', [RatingApiController::class, 'store']);
    Route::get('/', [RatingApiController::class, 'show']);
}); 