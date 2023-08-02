<?php

use App\Models\Problem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::group(['middleware' => 'jwt.auth'], function () {
    Route::get('/problems', 'ProblemController@index');
    Route::post('/problems', 'ProblemController@store')
        ->name('problems.create')->can('create', Problem::class);
    Route::get('/problems/{problem}', 'ProblemController@show')->can('view', 'problem');
    Route::post('/problems/{problem}', 'ProblemController@update')->can('update', 'problem');
    Route::delete('/problems/{problem}', 'ProblemController@delete')->can('delete', 'problem');
});

