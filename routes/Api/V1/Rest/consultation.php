<?php

use App\Models\Consultation;
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

Route::get('/consultations', 'ConsultationController@index');
Route::get('/consultations/{consultation}', 'ConsultationController@show');

Route::group(['middleware' => 'jwt.auth'], function () {
    Route::post('/consultations', 'ConsultationController@store')->can('create', Consultation::class);
    Route::post('/consultations/{consultation}', 'ConsultationController@update')->can('update', 'consultation');
    Route::delete('/consultations/{consultation}', 'ConsultationController@delete')->can('delete', 'consultation');
});
