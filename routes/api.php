<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\MovieApiController;
use App\Http\Controllers\Api\RatingApiController;
use App\Http\Controllers\Api\CommentApiController;
use App\Http\Controllers\Api\AuthController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Authentication routes
Route::post('login', [AuthController::class, 'login']);
Route::post('register', [AuthController::class, 'register']);

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Movie routes
Route::get('movies', [MovieApiController::class, 'index']);
Route::get('movies/{id}', [MovieApiController::class, 'show']);
Route::get('movies/search', [MovieApiController::class, 'search']);
Route::get('movies/{id}/statistics', [MovieApiController::class, 'statistics']);
Route::get('movies/{id}/comments', [CommentApiController::class, 'getMovieComments']);

// Protected routes that require authentication
Route::middleware('auth:sanctum')->group(function () {
    // Movie management
    Route::post('movies', [MovieApiController::class, 'store']);
    Route::put('movies/{id}', [MovieApiController::class, 'update']);
    Route::delete('movies/{id}', [MovieApiController::class, 'destroy']);
    
    // Ratings
    Route::post('ratings', [RatingApiController::class, 'store']);
    Route::put('ratings/{id}', [RatingApiController::class, 'update']);
    Route::get('movies/{id}/user-rating', [RatingApiController::class, 'getUserRating']);
    
    // Comments
    Route::post('comments', [CommentApiController::class, 'store']);
    Route::put('comments/{id}', [CommentApiController::class, 'update']);
    Route::delete('comments/{id}', [CommentApiController::class, 'destroy']);
    
    // User logout
    Route::post('logout', [AuthController::class, 'logout']);
}); 