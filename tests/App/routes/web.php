<?php

use Dongivan\RouteVersioning\Facades\Route;
use \Tests\App\App\Http\Controllers\TestV2Controller;
use \Tests\App\App\Http\Controllers\TestV1_10Controller;
use \Tests\App\App\Http\Controllers\TestV1_5Controller;
use \Tests\App\App\Http\Controllers\TestV1Controller;
use \Tests\App\App\Http\Controllers\TestController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::version("v2")->group(function () {
    Route::get("cached-test", [TestV2Controller::class, "index"]);
    Route::post("cached-test", [TestV2Controller::class, "store"]);
    Route::get("cached-test/{id}", [TestV2Controller::class, "show"]);
});

Route::version("v1.10")->get("cached-test", [TestV1_10Controller::class, "index"]);
Route::version("v1.5")->get("cached-test", [TestV1_5Controller::class, "index"]);

Route::version("v1")->group(function () {
    Route::get("cached-test", [TestV1Controller::class, "index"]);
    Route::post("cached-test", [TestV1Controller::class, "store"]);
    Route::get("cached-test/{id}", [TestV1Controller::class, "show"]);
});

Route::get("cached-test", [TestController::class, "index"]);
Route::post("cached-test", [TestController::class, "store"]);
Route::get("cached-test/{id}", [TestController::class, "show"]);
