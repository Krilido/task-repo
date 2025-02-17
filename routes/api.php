<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('section')->group(function () {
    Route::post('create', 'SectionController@store');
    Route::get('{id}', 'SectionController@show');
    Route::get('withtask/{id}', 'SectionController@showWithTask');
    Route::post('update', 'SectionController@update');
    Route::get('', 'SectionController@index');
});

Route::prefix('task')->group(function () {
    Route::post('create', 'TaskController@store');
    Route::get('{id}', 'TaskController@show');
    Route::get('withsection/{id}', 'TaskController@showWithSection');
    Route::post('update', 'TaskController@update');
    Route::get('', 'TaskController@index');
});