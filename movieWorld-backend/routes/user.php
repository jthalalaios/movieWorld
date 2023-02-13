<?php

use App\Http\Controllers\User\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| User's API Routes
|--------------------------------------------------------------------------
*/


// Resource user
Route::resource('user', UserController::class);
