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
    Route::get('/whois/{id}/', 'APIController@whois');
    Route::get('/registration/{id}/{server?}', 'APIController@getAllyFromDiscord');
    Route::post('/registration/{id}/{discord}/{server?}', 'APIController@register');
    Route::delete('/registration/{id}/{server?}', 'APIController@deleteRegistration');
    Route::post('/guild-query-response', 'APIController@guildQueryResponse')->name('bot.guild.response');
});
