<?php

use App\Http\Controllers\AuthController;
use Laravel\Sanctum\Http\Controllers\CsrfCookieController;

Route::middleware('web')->group(function () {

    

    // 🔹 CSRF cookie
    Route::get('/sanctum/csrf-cookie', [CsrfCookieController::class, 'show']);


});
