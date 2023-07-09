<?php

use App\Models\PaymentPlan;
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
Route::get('/payment-plans', 'PaymentPlanController@index');
Route::get('/payment-plans/{paymentPlan}', 'PaymentPlanController@show');

Route::group(['middleware' => 'jwt.auth'], function () {
    Route::post('/payment-plans', 'PaymentPlanController@store')
        ->can('create', PaymentPlan::class);
    Route::post('/payment-plans/{paymentPlan}', 'PaymentPlanController@update')
        ->can('update', 'paymentPlan');
    Route::delete('/payment-plans/{paymentPlan}', 'PaymentPlanController@delete')
        ->can('delete', 'paymentPlan');
});
