<?php

use App\Http\Controllers\Movie\MovieController;
use App\Http\Controllers\Movie\MovieHateController;
use App\Http\Controllers\Movie\MovieLikeController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Movie's API Routes
|--------------------------------------------------------------------------
*/

// Resource movie
Route::resource('movie', MovieController::class);


// POST /movie/{movie_id}/like
Route::post('/movie/{movie_id}/like', [MovieLikeController::class, 'store'])
    ->name('movie.like');

// POST /movie/{movie_id}/like
Route::post('/movie/{movie_id}/hate', [MovieHateController::class, 'store'])
    ->name('movie.hate');
