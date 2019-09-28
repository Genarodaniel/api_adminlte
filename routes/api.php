<?php

use Illuminate\Http\Request;

Route::prefix('api_user')->group(function(){
	// api user routes
	Route::post('login','UserController@login')->name('api_user.login');
	Route::post('register','UserController@register')->name('api_user.store');

	Route::group(['middleware' =>'auth:api'], function(){
		Route::get('details','UserController@details')->name('api_user.details');
	});
});
	// app users routes
	Route::group(['middleware' =>'auth:api'], function(){

		Route::prefix('user')->group(function(){
			Route::post('login','User_appController@login')->name('user.login');
			Route::get('list', 'User_appController@all_users')->name('user.list');
			Route::post('add','User_appController@store')->name('user.store');
			Route::get('get/{id}', 'User_appController@show')->name('user.show');
			Route::put('update/{id}', 'User_appController@update')->name('user.update');

		});

		Route::prefix('condominium')->group(function(){
			Route::get('list','CondominiumController@list')->name('condominium.list');
			Route::post('add','CondominiumController@store')->name('condominium.store');
			route::get('get/{id}','CondominiumController@show')->name('condominium.show');
			route::put('update/{id}','CondominiumController@update')->name('condominium.update');
		});

	});


