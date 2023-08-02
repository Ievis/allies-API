<?php

use App\Models\Tag;
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

Route::get('/tags', 'TagController@index');
Route::get('/tags/{tag}', 'TagController@show');

Route::group(['middleware' => 'jwt.auth'], function () {
    Route::post('/tags', 'TagController@store')->can('create', Tag::class);
    Route::post('/tags/{tag}', 'TagController@update')->can('update', 'tag');
    Route::delete('/tags/{tag}', 'TagController@delete')->can('delete', 'tag');
});
