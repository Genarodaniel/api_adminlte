<?php

use Illuminate\Http\Request;

Route::get('/data', 'DataController@index')->name('path.index');
Route::post('/data', 'DataController@store')->name('path.store');
Route::get('/data/{id}', 'DataController@show')->name('path.show');
Route::put('/data/{data}', 'DataController@update')->name('path.update');
Route::delete('/data/{r}', 'DataController@destroy')->name('path.destroy');
//Route::get('/ok','ResidentController@all_users')->name('ok');


Route::get('/condominiums','CondominiumController@list');
Route::post('/condominium','CondominiumController@store');
route::get('/condominium/{id}','CondominiumController@show');
route::post('update','CondominiumController@update');

Route::prefix('users')->group(function(){ 
	
	Route::post('login','UserController@login');
	Route::post('login_app','User_appController@login');
	Route::post('register','UserController@register');

	Route::group(['middleware' =>'auth:api'], function(){
		Route::get('details','UserController@details');
		Route::get('/', 'User_appController@all_users')->name('all_users');
		Route::post('add_user','User_appController@store')->name('add_user');
		Route::get('/{id}', 'User_appController@show')->name('single_user');
	});

});

	Route::post('add_user','User_appController@store')->name('add_user');
	Route::get('/users_api', 'UserController@all_users')->name('all_users');
	Route::get('/users_api/{id}', 'UserController@show')->name('single_user');
	 

	//Route::prefix('users')->group(function(){
	//route::get('/users','ResidentController@all_users')->name('all_users');
	//Route::get('/ok',function(){
		//return ['status'=>true];
	//});
	
	

?>