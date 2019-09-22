<?php

use Illuminate\Http\Request;

Route::prefix('api_user')->group(function(){
	// api user routes
	Route::post('login','UserController@login');
	Route::post('register','UserController@register');

	Route::group(['middleware' =>'auth:api'], function(){
		Route::get('details','UserController@details');
	});
});
	// app users routes
	Route::group(['middleware' =>'auth:api'], function(){

		Route::prefix('user')->group(function(){
			Route::post('login','User_appController@login');
			Route::get('/list', 'User_appController@all_users')->name('all_users');
			Route::post('add','User_appController@store')->name('add_user');
			Route::get('/get/{id}', 'User_appController@show')->name('single_user');

		});

		Route::prefix('condominium')->group(function(){
			Route::get('/list','CondominiumController@list');
			Route::post('/add','CondominiumController@store');
			route::get('/{id}','CondominiumController@show');
			route::put('update/{id}','CondominiumController@update');
		});

	});






?>