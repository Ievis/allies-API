<?php

use App\Models\User;
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
Route::get('/users', 'UserController@index');

Route::group(['middleware' => 'jwt.auth'], function () {
    Route::post('/users/telegram', 'TelegramUserController@store');
    Route::get('/users/{user}', 'UserController@show')->can('view', 'user');
    Route::post('/users', 'UserController@store')->name('users.create')->can('create', User::class);
    Route::post('/users/{user}', 'UserController@update')->can('update', 'user');
    Route::delete('/users/{user}', 'UserController@delete')->can('delete', 'user');
});
