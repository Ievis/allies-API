<?php

use App\Models\Subject;
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

Route::get('/subjects', 'SubjectController@index');
Route::get('/subjects/{subject}', 'SubjectController@show');

Route::group(['middleware' => 'jwt.auth'], function () {
    Route::post('/subjects', 'SubjectController@store')->can('create', Subject::class);
    Route::post('/subjects/{subject}', 'SubjectController@update')->can('update', 'subject');
    Route::delete('/subjects/{subject}', 'SubjectController@delete')->can('delete', 'subject');
});
