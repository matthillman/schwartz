<?php

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

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/mods', 'ModsController@index')->name('mods');
Route::get('/mods/{user}', 'ModsController@pullUser')->name('mods.user');

Route::group(['middleware' => ['auth:web,admin']], function() {
    Route::group(['middleware' => ['active']], function() {
        Route::get('/handbook/{name}', 'HandbookController')->name('handbook');
        Route::get('/guide/{name}', 'GuideController')->name('guide');
        Route::get('/home', 'HomeController@index')->name('home');
        Route::get('/home/mods', 'ModsController@index')->name('auth.mods');
        Route::get('/home/mods/{user}', 'ModsController@pullUser')->name('auth.mods.user');
    });

    Route::name('approve.')->middleware(['auth:admin'])->group(function() {
        Route::put('/approve/{id}', 'HomeController@approveUser')->name('user');
        Route::put('/approve/admin/{id}', 'HomeController@approveAdmin')->name('admin');
    });

    Route::get('/units', 'UnitController@index')->name('units');
    Route::get('/guilds', 'GuildController@listGuilds')->name('guilds');
    Route::post('/guilds', 'GuildController@addGuild')->name('guild.add');
    Route::get('/guild/{guild}', 'GuildController@listMembers')->name('guild.members');

    Route::get('/waiting', 'HomeController@waiting')->name('waiting');
});
