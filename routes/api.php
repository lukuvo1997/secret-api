<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// use Models
use App\Http\Controllers\SecretController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


// a group segítségével el tudjuk különíteni a későbbi verziókat

Route::prefix('v1')->group(function () {
    Route::get('/secret/{hash}', [SecretController::class, 'getSecretByHash']);
    Route::post('/secret', [SecretController::class, 'addSecret']);
});

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
