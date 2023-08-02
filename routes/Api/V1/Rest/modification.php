<?php

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
    Route::get('/modifications', 'ModificationController@index');

//    Route::post('/modifications', 'ModificationController@store')
//        ->middleware('can:create, modification');
    Route::get('/modifications/{modification}', 'ModificationController@show')
        ->can('view', 'modification');
    Route::post('/modifications/{modification}', 'ModificationController@update')
        ->can('update', 'modification');
    Route::delete('/modifications/{modification}', 'ModificationController@delete')
        ->can('delete', 'modification');
});
