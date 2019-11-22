<?php

use Illuminate\Http\Request;

	Route::prefix('api_user')->group(function(){
	// api user routes
		Route::post('login','UserController@login')->name('api_user.login');
		Route::post('register','UserController@register')->name('api_user.store');
		Route::delete('delete','UserController@delete')->name('api_user.delete');

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
			Route::delete('delete/{id}','User_appController@delete')->name('user.delete');

		});

		Route::prefix('condominium')->group(function(){
			Route::get('list','CondominiumController@list')->name('condominium.list');
			Route::post('add','CondominiumController@store')->name('condominium.store');
			route::get('get/{id}','CondominiumController@show')->name('condominium.show');
			route::put('update/{id}','CondominiumController@update')->name('condominium.update');
			Route::delete('delete/{id}','CondominiumController@delete')->name('condominium.delete');
		});

		Route::prefix('utensil')->group(function(){
			Route::get('list','UtensilController@list')->name('utensil.list');
			Route::post('add','UtensilController@store')->name('utensil.store');
			Route::put('update/{id}','UtensilController@update')->name('utensil.update');
			Route::get('get/{id}','UtensilController@show')->name('utensil.show');
			Route::delete('delete/{id}','UtensilController@delete')->name('utensil.delete');

			Route::post('add_schedule','UtensilScheduleController@store')->name('utensilSchedule.store');
			Route::put('update_schedule','UtensilScheduleController@update')->name('utensilSchedule.update');
			Route::get('list_schedules/{utensil_id}','UtensilScheduleController@list')->name('UtensilSchedule.list');
			Route::delete('delete_schedule/{id}','UtensilScheduleController@delete')->name('utensilSchedule.delete');
		});

		Route::prefix('reserve')->group(function(){
			Route::post('add','ReservController@store')->name('reserve.store');
			Route::post('listReserv','ReservController@listByDate')->name('reserve.listByDate');
			Route::post('listReserv','ReservController@listByUser')->name('reserve.listByUser');
			Route::post('listReserv','ReservController@listByUtensil')->name('reserve.listByUtensil');
			Route::put('update','ReservController@update')->name('reserve.update');
			Route::delete('delete/{id}','ReservController@delete')->name('reserve.delete');

		});

	});

	Route::get('ok',function(){
		return response()->json(['ok'=>'ok']);
	});

