<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MovieController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\RatingController;
use App\Http\Controllers\MediaController;

Route::get('/', function () {
    return redirect()->route('movies.index');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Movie routes
Route::resource('movies', MovieController::class)
    ->except(['index', 'show', 'search'])
    ->middleware('auth');

Route::get('/movies', [MovieController::class, 'index'])->name('movies.index');
Route::get('/movies/{movie}', [MovieController::class, 'show'])->name('movies.show');
Route::get('/search', [MovieController::class, 'search'])->name('movies.search');
Route::get('/movies/{id}/statistics', [MovieController::class, 'statistics'])->name('movies.statistics');

// Comment routes
Route::post('/comments', [CommentController::class, 'store'])->name('comments.store');
Route::put('/comments/{id}', [CommentController::class, 'update'])->name('comments.update');
Route::delete('/comments/{id}', [CommentController::class, 'destroy'])->name('comments.destroy');

// Rating routes
Route::post('/ratings', [RatingController::class, 'store'])->name('ratings.store');
Route::put('/ratings/{id}', [RatingController::class, 'update'])->name('ratings.update');

// Media routes
Route::post('/media', [MediaController::class, 'store'])->name('media.store');
Route::put('/media/{id}', [MediaController::class, 'update'])->name('media.update');
Route::delete('/media/{id}', [MediaController::class, 'destroy'])->name('media.destroy');

// Include Auth Routes
require __DIR__.'/auth.php';
