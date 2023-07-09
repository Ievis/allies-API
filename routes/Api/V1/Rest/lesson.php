<?php

use App\Models\Lesson;
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
    Route::get('/lessons', 'LessonController@index');
    Route::post('/lessons', 'LessonController@store')
        ->name('lessons.create')->can('create', Lesson::class);
    Route::get('/lessons/{lesson}', 'LessonController@show')->can('view', 'lesson');
    Route::post('/lessons/{lesson}', 'LessonController@update')->can('update', 'lesson');
    Route::delete('/lessons/{lesson}', 'LessonController@delete')->can('delete', 'lesson');
});
