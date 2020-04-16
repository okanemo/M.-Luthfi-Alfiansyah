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

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });
Route::post('login', 'UserController@login');
Route::group(['middleware' => 'auth:api'], function()
{
    Route::post('logout','UserController@logout');
    Route::get('show/user/all','UserController@getAllUser');
    Route::get('show/user/{id}','UserController@showUserById');
    Route::get('edit/user/{id}','UserController@editUser');
    Route::post('details/user', 'UserController@details');
    Route::post('store/user', 'UserController@storeUser');
    Route::put('update/user/{id}', 'UserController@updateUser');
    Route::delete('destroy/user/{id}', 'UserController@destroyUser');
});
