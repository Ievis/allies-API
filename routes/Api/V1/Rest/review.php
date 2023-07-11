<?php

use App\Models\Review;
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

Route::get('/reviews', 'ReviewController@index');
Route::get('/reviews/{review}', 'ReviewController@show');

Route::group(['middleware' => 'jwt.auth'], function () {
    Route::post('/reviews', 'ReviewController@store')->can('create', Review::class);
    Route::post('/reviews/{review}', 'ReviewController@update')->can('update', 'review');
    Route::delete('/reviews/{review}', 'ReviewController@delete')->can('delete', 'review');
});
