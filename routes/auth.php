<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('/signin', [AuthController::class, 'signin'])->middleware('throttle:3,3');
    // Route::post('/register', [AuthController::class, 'register']);
    //swagger a dn test is also done
    Route::post('/refresh', [AuthController::class, 'refresh']);
    Route::post('/signout', [AuthController::class, 'signout']);
});
