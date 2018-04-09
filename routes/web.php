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

Route::group(['middleware' => ['auth:web,admin']], function() {
    Route::group(['middleware' => ['active']], function() {
        Route::get('/page/{page}', 'MarkdownController')->name('guide');
        Route::get('/home', 'HomeController@index')->name('home');
    });

    Route::name('approve.')->middleware(['auth:admin'])->group(function() {
        Route::put('/approve/{id}', 'HomeController@approveUser')->name('user');
        Route::put('/approve/admin/{id}', 'HomeController@approveAdmin')->name('admin');
    });

    Route::get('/waiting', 'HomeController@waiting')->name('waiting');
});
