<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function (Request $request) {
    $routeCollection = Illuminate\Support\Facades\Route::getRoutes()->getRoutes();
    $defaultRoutes = ['sanctum/csrf-cookie', '_ignition/health-check', '_ignition/execute-solution', '_ignition/update-config'];
    foreach ($routeCollection as $route) {
        if (in_array($route->uri, $defaultRoutes)) continue;
        echo "<h2>$route->uri</h2>";
    }
});
