<?php

/*
|--------------------------------------------------------------------------
| Application & Route Filters
|--------------------------------------------------------------------------
|
| Below you will find the "before" and "after" events for the application
| which may be used to do any work before or after a request into your
| application. Here you may also register your custom route filters.
|
*/

Route::filter('allowOrigin', function($route, $request, $response) 
{
    $response->header('access-control-allow-origin','*');
    $response->header('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');
    
});

Route::filter('apiAuth', function() 
{

	/** 
	* Bisa menggunakan header atau key(get)
	* Pertama check header dulu
	*
	**/

	// pre defined key
	$key = '';
	
	if(trim(Request::header('Authorization')) !== ''){

		$key = Request::header('Authorization');

	}else if (trim(Input::get('_key')) !== '') {

		$key = Input::get('_key');

	}

    $loginAPi = Tbl_token::where('token_data',$key)->first();
    
    /* jika ada token langsung pass */
    if(Request::header('X-CSRF-Token') == Session::token()){

    }else{

	    if(is_object($loginAPi)){

	    	// check expired
	    	if( date('YmdHms', strtotime($loginAPi->token_expires_on)) > date('YmdHms', strtotime(date('YmdHms'))) == false){

				return Response::json(array(

					'code' => 403,
					'message' => 'Token has been expired',
					'type' => 'error'

					));
	    	}

	    }else{

			return Response::json(array(

				'code' => 405,
				'message' => 'Unauthorized Access',
				'type' => 'error'

				));
	    }
	}
    
});

/*
|--------------------------------------------------------------------------
| Authentication Filters
|--------------------------------------------------------------------------
|
| The following filters are used to verify that the user of the current
| session is logged into this application. The "basic" filter easily
| integrates HTTP Basic authentication for quick, simple checking.
|
*/

Route::filter('auth', function()
{
	if (Auth::guest()) return Redirect::guest('login');
});


Route::filter('auth.basic', function()
{
	return Auth::basic();
});

/*
|--------------------------------------------------------------------------
| Guest Filter
|--------------------------------------------------------------------------
|
| The "guest" filter is the counterpart of the authentication filters as
| it simply checks that the current user is not logged in. A redirect
| response will be issued if they are, which you may freely change.
|
*/

Route::filter('guest', function()
{
	if (Auth::check()) return Redirect::to('/');
});

/*
|--------------------------------------------------------------------------
| CSRF Protection Filter
|--------------------------------------------------------------------------
|
| The CSRF filter is responsible for protecting your application against
| cross-site request forgery attacks. If this special token in a user
| session does not match the one given in this request, we'll bail.
|
*/

Route::filter('csrf', function()
{
	if (Session::token() != Input::get('_token'))
	{
		throw new Illuminate\Session\TokenMismatchException;
	}
});