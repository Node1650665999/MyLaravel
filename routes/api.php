<?php

use Illuminate\Http\Request;

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

/*Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});*/

Route::any('login', 'ApiController@login');
Route::any('register', 'ApiController@register');
Route::any('test', 'TestController@userInfo');

Route::middleware('auth.jwt')->group(function () {
    Route::any('logout', 'ApiController@logout');
    Route::any('user', 'ApiController@getAuthUser');
});



