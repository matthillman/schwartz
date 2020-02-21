<?php

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

Route::group(['middleware' => 'client'], function() {
    Route::get('/guild/scrape/{id}', 'APIController@scrapeGuild');
    Route::get('/member/scrape/{id}', 'APIController@scrapeMember');
    Route::get('/registration/{id}', 'APIController@getAllyFromDiscord');
    Route::post('/registration/{id}/{discord}', 'APIController@register');
    Route::delete('/registration/{id}', 'APIController@deleteRegistration');
});
