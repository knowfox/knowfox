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

    Route::get('/concepts/toplevel', [
        'as' => 'concept.toplevel',
        'uses' => 'ConceptController@toplevel',
    ]);

    Route::get('/concepts/flagged', [
        'as' => 'concept.flagged',
        'uses' => 'ConceptController@flagged',
    ]);

    Route::get('/concepts', [
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

    Route::get('/concept/{concept}', function ($concept) {
        return redirect()->route('concept.show', [$concept]);
    });

    // So that images without a path work
    Route::get('/concept/{concept}/view', [
        'as' => 'concept.show',
        'uses' => 'ConceptController@show',
    ]);

    Route::get('/concept/{concept}/graph', [
        'as' => 'concept.graph',
        'uses' => 'ConceptController@graph',
    ]);

    Route::delete('/concept/{concept}', [
        'as' => 'concept.destroy',
        'uses' => 'ConceptController@destroy',
    ]);

    Route::post('/concept/{concept}', [
        'as' => 'concept.update',
        'uses' => 'ConceptController@update',
    ]);

    Route::get('/concept/{concept}/{filename}', [
        'as' => 'concept.image',
        'uses' => 'ConceptController@image',
    ]);

    Route::post('/upload/{uuid}', [
        'as' => 'concept.upload',
        'uses' => 'ConceptController@upload',
    ]);

    Route::get('/images/{concept}', [
        'as' => 'concept.images',
        'uses' => 'ConceptController@images',
    ]);

});

