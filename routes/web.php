<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});


// a route segítségével képesek vagyunk böngészőből migrációt és adatbázis seed-et véghez vinni 

Route::get('/db-migrate', function () {
    Artisan::queue('migrate:fresh --seed');
});
