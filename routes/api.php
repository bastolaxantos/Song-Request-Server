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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('queue', 'QueueController@addToQueue');
Route::get('queue', 'QueueController@getQueue');
Route::delete('queue', 'QueueController@deleteFromQueue');
Route::get('playqueue', 'QueueController@getPlayQueue');
Route::post('vote', 'QueueController@changeVote');
Route::post('playing', 'QueueController@markPlaying');
Route::post('played', 'QueueController@markPlayed');
