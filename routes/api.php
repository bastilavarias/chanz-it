<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ParseController;
use App\Http\Controllers\ConversionController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

//Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//    return $request->user();
//});

Route::prefix('parse')->group(function () {
    Route::post('/number', [ParseController::class, 'toNumber']);
    Route::post('/word', [ParseController::class, 'toWords']);
});

Route::prefix('conversion')->group(function () {
    Route::post('/usd', [ConversionController::class, 'toUSD']);
});
