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

Route::post('/register', 'Api\AuthController@register');
Route::post('/login', 'Api\AuthController@login');
Route::group(['middleware' => 'auth:api', 'prefix' => 'Api'], function () {
    // Single chat
    Route::post('/chat', 'ChatController@chat');
    // Group chat
    Route::post('/chat-group', 'ChatController@chatGroup');
    // Group
    Route::post('/create-group', 'GroupController@store');
    Route::post('/update-group/{id}', 'GroupController@update');
    Route::delete('/delete-group', 'GroupController@destroy');
    Route::post('/add-member-group', 'GroupController@addMember');
    Route::delete('/remove-member-group', 'GroupController@removeMember');
});

