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

Route::get('/home', 'HomeController@index');

Route::group(['middleware' => 'auth'], function () {
    Route::get('/tags', [
        'as' => 'tags.index',
        'uses' => 'TagsController@index',
    ]);

    Route::get('/concepts/{flagged?}', [
        'as' => 'concept.index',
        'uses' => 'ConceptController@index',
    ]);

    Route::get('/concept/create', [
        'as' => 'concept.create',
        'uses' => 'ConceptController@create',
    ]);

    Route::post('/concept/store', [
        'as' => 'concept.store',
        'uses' => 'ConceptController@store',
    ]);

    Route::get('/concept/{concept}', [
        'as' => 'concept.show',
        'uses' => 'ConceptController@show',
    ]);

    Route::delete('/concept/{concept}', [
        'as' => 'concept.destroy',
        'uses' => 'ConceptController@destroy',
    ]);

    Route::post('/concept/{concept}', [
        'as' => 'concept.update',
        'uses' => 'ConceptController@update',
    ]);

    // http://knowfox.dev/images/6245dda0/f867/11e6/822e/081cda0f15f6/512x1024/square.jpeg

    Route::get('/images/{uuid1}/{uuid2}/{uuid3}/{uuid4}/{uuid5}/{image}/{style}.jpeg', [
        'as' => 'concept.image',
        'uses' => 'ConceptController@image',
    ]);

    Route::post('/upload/{uuid}', [
        'as' => 'concept.upload',
        'uses' => 'ConceptController@upload',
    ]);

    Route::get('/attachments/{concept}', [
        'as' => 'concept.attachments',
        'uses' => 'ConceptController@attachments',
    ]);
});

