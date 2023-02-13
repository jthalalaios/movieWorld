<?php

use App\Http\Controllers\ApiToken\ApiTokenController;

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Auth
|--------------------------------------------------------------------------
*/

// /*
// |--------------------------------------------------------------------------
// | Sanctum Tokens
// |--------------------------------------------------------------------------
// */

// POST /login
Route::post('/login', [ApiTokenController::class, 'store'])
    ->name('login');

// DELETE /logout
Route::delete('/logout', [ApiTokenController::class, 'destroy'])
    ->name('logout')->middleware(['auth:sanctum']);
