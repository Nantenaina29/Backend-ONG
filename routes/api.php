<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\MembreController;
use App\Http\Controllers\GsController;
use App\Http\Controllers\ReseauController;
use App\Http\Controllers\ResponsableController;
use App\Http\Controllers\FormationController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\TrashController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Artisan;

/*
|--------------------------------------------------------------------------
| PUBLIC AUTH ROUTES (No token required)
|--------------------------------------------------------------------------
*/
Route::post('/login', [AuthController::class, 'login']);           // Frontend: POST /api/login
Route::post('/register', [AuthController::class, 'register']);     // Frontend: POST /api/register
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']); // Frontend: POST /api/forgot-password

/*
|--------------------------------------------------------------------------
| PROTECTED ROUTES (Auth:sanctum required)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {
    
    // AUTH
    Route::get('/user', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
    
    // USER PROFILE
    Route::prefix('profile')->group(function () {
        Route::post('/update', [UserController::class, 'updateProfile']);
        Route::post('/photo', [UserController::class, 'updatePhoto']);
        Route::post('/password', [UserController::class, 'changePassword']);
    });

    // RESPONSABLES
    Route::prefix('responsables')->group(function () {
        Route::get('/', [ResponsableController::class, 'index']);
        Route::get('/femmes-bureau', [ResponsableController::class, 'getFemmesBureau']);
        Route::put('/{id}', [ResponsableController::class, 'update']);
    });

    // NOTIFICATIONS (Admin only)
    Route::prefix('notifications')->group(function () {
        Route::get('/', [NotificationController::class, 'index']);
        Route::delete('/{id}', [NotificationController::class, 'destroy']);
    });

    // MEMBRES
    Route::prefix('membres')->group(function () {
        Route::get('/', [MembreController::class, 'index']);
        Route::post('/', [MembreController::class, 'store']);
        Route::get('/{id}', [MembreController::class, 'show']);
        Route::put('/{id}', [MembreController::class, 'update']);
        Route::delete('/{id}', [MembreController::class, 'destroy']);
    });

    // GS (Groupes Solidarité)
    Route::prefix('gs')->group(function () {
        Route::get('/', [GsController::class, 'index']);
        Route::get('/menages', [GsController::class, 'getNumMenages']);
        Route::post('/', [GsController::class, 'store']);
        Route::put('/{id}', [GsController::class, 'update']);
        Route::delete('/{id}', [GsController::class, 'destroy']);
    });

    // RESEAUX
    Route::prefix('reseaux')->group(function () {
        Route::get('/', [ReseauController::class, 'index']);
        Route::get('/{id}', [ReseauController::class, 'show']);
        Route::get('/gs-list', [ReseauController::class, 'gsList']);
        Route::post('/', [ReseauController::class, 'store']);
        Route::put('/{id}', [ReseauController::class, 'update']);
        Route::delete('/{id}', [ReseauController::class, 'destroy']);
    });

    // FORMATIONS
    Route::prefix('formations')->group(function () {
        Route::get('/', [FormationController::class, 'index']);
        Route::post('/', [FormationController::class, 'store']);
    });

    // TRASH (Admin only)
    Route::prefix('trash')->group(function () {
        Route::get('/{table}', [TrashController::class, 'index']);
        Route::post('/{table}/{id}/restore', [TrashController::class, 'restore']);
        Route::delete('/{table}/{id}/force', [TrashController::class, 'forceDelete']);
    });

    // DEBUG
    Route::get('/test', function() { 
        return response()->json(['message' => 'API OK', 'user' => auth()->user()]); 
    });
});

/*
|--------------------------------------------------------------------------
| DEV/DEBUG ROUTES (Esory production)
|--------------------------------------------------------------------------
*/
Route::get('/init-db', function () {
    Artisan::call('migrate', ['--force' => true]);
    return "Database efa vonona!";
});
