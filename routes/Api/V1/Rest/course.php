<?php

use App\Models\Course;
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

Route::get('/courses', 'CourseController@index')->name('courses.index');
Route::get('/courses/{course}', 'CourseController@show');

Route::group(['middleware' => 'jwt.auth'], function () {
    Route::post('/courses', 'CourseController@store')->name('courses.create')->can('create', Course::class);
    Route::post('/courses/{course}', 'CourseController@update')->name('courses.update')->can('update', 'course');
    Route::delete('/courses/{course}', 'CourseController@delete')->can('delete', 'course');
});

