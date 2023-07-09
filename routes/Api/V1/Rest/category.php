<?php

use App\Models\Category;
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

Route::get('/categories', 'CategoryController@index');
Route::get('/categories/{category}', 'CategoryController@show');

Route::group(['middleware' => 'jwt.auth'], function () {
    Route::post('/categories', 'CategoryController@store')->can('create', Category::class);
    Route::post('/categories/{category}', 'CategoryController@update')->can('update', 'category');
    Route::delete('/categories/{category}', 'CategoryController@delete')->can('delete', 'category');
});
