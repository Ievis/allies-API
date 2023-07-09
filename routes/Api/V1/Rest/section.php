<?php

use App\Models\Section;
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

Route::get('/sections', 'SectionController@index');
Route::get('/sections/{section}', 'SectionController@show');

Route::group(['middleware' => 'jwt.auth'], function () {
    Route::post('/sections', 'SectionController@store')->can('create', Section::class);
    Route::post('/sections/{section}', 'SectionController@update')->can('update', 'section');
    Route::delete('/sections/{section}', 'SectionController@delete')->can('delete', 'section');
});
