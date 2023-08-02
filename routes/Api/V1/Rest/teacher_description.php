<?php

use App\Models\TeacherDescription;
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

Route::get('/teacher-descriptions', 'TeacherDescriptionController@index');
Route::get('/teacher-descriptions/{teacherDescription}', 'TeacherDescriptionController@show');

Route::group(['middleware' => 'jwt.auth'], function () {
    Route::post('/teacher-descriptions', 'TeacherDescriptionController@store')->can('create', TeacherDescription::class);
    Route::post('/teacher-descriptions/{teacherDescription}', 'TeacherDescriptionController@update')->can('update', 'teacherDescription');
    Route::delete('/teacher-descriptions/{teacherDescription}', 'TeacherDescriptionController@delete')->can('delete', 'teacherDescription');
});
