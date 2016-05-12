<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the Closure to execute when that URI is requested.
|
*/

Route::get('/', function()
{
	return View::make('hello');
});

Route::group(['prefix' => 'api','before' => 'apiAuth','after' => 'allowOrigin'], function() {

	Route::group(['prefix' => 'user'], function() {

		Route::get('/', 'UserController@index');// get

		Route::post('/', 'UserController@store');//create /store

		Route::get('/{id}', 'UserController@show');//data satu

		Route::post('/{id}/photo', 'UserController@photoUpload');//login

		Route::post('/{id}/profile', 'UserController@profile');//update

		Route::post('/{id}/social', 'UserController@social');//update

		Route::post('/{id}/security', 'UserController@security');//update

		Route::post('/{id}/email', 'UserController@email');//update

		Route::post('/{id}/notif/read', 'UserController@notif');//update

		Route::get('/{id}/notif', 'NotifController@getNotifUser');//update

		Route::get('/{id}/notifications', 'NotifController@getNotifUserIndex');//update

		Route::post('/{id}/notif', 'NotifController@updateNotifSeen');//update

	});

	Route::group(['prefix' => 'message'], function() {

		Route::get('/{id}', 'MessageController@index');//create/store

		Route::get('/{id}/messages/{conv}', 'MessageController@indexMessage');//create/store

		Route::post('/{from}/store/{to}', 'MessageController@store');//create/store

		Route::post('/{id}/store/{from}/message', 'MessageController@storeMessage');//create/store
		
		Route::get('/{id}/conversation/{conv}/delete', 'MessageController@destroy');//create/store

	});

});

Route::group(['prefix' => 'pub','after' => 'allowOrigin'], function() {

	Route::post('register', 'UserController@store');//create/store

	Route::post('login', 'HomeController@doLogin');

	Route::post('validation', 'UserController@validationConfirmCode');//data satu

});
