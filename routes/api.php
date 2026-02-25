<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
| AUTH Routes (Public)
|--------------------------------------------------------------------------
*/
Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::post('/register', [AuthController::class, 'register']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);



/*
|--------------------------------------------------------------------------
| PROTECTED Routes (Mila Login daholo ny eto ambany)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {

    //Parametres
    Route::post('/update-profile', [UserController::class, 'updateProfile']);
    Route::post('/update-photo', [UserController::class, 'updatePhoto']);
    Route::post('/change-password', [UserController::class, 'changePassword']);

    Route::get('/femmes-bureau', [ResponsableController::class, 'getFemmesBureau']);
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::delete('/notifications/{id}', [NotificationController::class, 'destroy']);
    
    // Auth info
    Route::get('/user', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/test', function() { return response()->json(['message' => 'API OK', 'status' => 'Authenticated']); });

    /*
    |----------------------------------------------------------------------
    | MEMBRES
    |----------------------------------------------------------------------
    */
    // Ny 'index' dia tsy asiana 'owner' mba hahafahan'ny Controller manasivana araka ny Role (Admin/User)
    Route::get('/test-respo', function() {
        return response()->json(DB::table('responsables')->get());
    });
    
    Route::get('/membres', [MembreController::class, 'index']);
    
    Route::post('/membres', [MembreController::class, 'store']);


    Route::get('/membres/{id}', [MembreController::class, 'show']);
    Route::put('/membres/{id}', [MembreController::class, 'update']); 
    Route::delete('/membres/{id}', [MembreController::class, 'destroy']); 

    /*
    |----------------------------------------------------------------------
    | GS & RESEAUX (Ampiharo koa ilay logique raha ilaina)
    |----------------------------------------------------------------------
    */
    Route::get('/gs', [GsController::class, 'index']);
    Route::post('/gs', [GsController::class, 'store']);
    Route::put('/gs/{id}', [GsController::class, 'update'])->middleware('owner');
    Route::delete('/gs/{id}', [GsController::class, 'destroy'])->middleware('owner');
    
    // Public/Shared data ho an'ny formulaire
    Route::get('/menages', [GsController::class, 'getNumMenages']);


    // FORMATIONS
    Route::get('/formations', [FormationController::class, 'index']);
    Route::post('/formations', [FormationController::class, 'store']);

    // RESEAUX
    Route::get('/reseaux', [ReseauController::class, 'index']);
    Route::get('/reseaux/{id}', [ReseauController::class, 'show']);
    Route::post('/reseaux', [ReseauController::class, 'store']);
    
    // Esory ny ->middleware('owner') eto
    Route::put('/reseaux/{id}', [ReseauController::class, 'update']);
    Route::delete('/reseaux/{id}', [ReseauController::class, 'destroy']);
    Route::get('/gs-list', [ReseauController::class, 'gsList']);

        //RESPONSABLE
        Route::put('/responsables/{id}', [ResponsableController::class, 'update']);
        Route::get('/responsables', [ResponsableController::class, 'index']);

        Route::prefix('trash')->group(function () {
            Route::get('/{table}', [TrashController::class, 'index']); // Hijery ny lisitra
            Route::post('/{table}/{id}/restore', [TrashController::class, 'restore']); // Famerenana
            Route::delete('/{table}/{id}/force', [TrashController::class, 'forceDelete']); // Famafana tanteraka
        });
});

Route::get('/init-db', function () {
    Artisan::call('migrate', ['--force' => true]);
    return "Database efa vonona!";
});



