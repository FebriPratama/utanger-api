<?php

class HomeController extends BaseController {

	/*
	|--------------------------------------------------------------------------
	| Default Home Controller
	|--------------------------------------------------------------------------
	|
	| You may wish to use controllers instead of, or in addition to, Closure
	| based routes. That's great! Here is an example controller method to
	| get you started. To route to this controller, just add the route:
	|
	|	Route::get('/', 'HomeController@showWelcome');
	|
	*/

	public function showWelcome()
	{
		return View::make('hello');
	}

	public function doLogin(){

		$data = array(
			
			'email' => Input::get('email'),
			'password' => Input::get('password')

			);

		$validation = Validator::make($data,Tbl_user::$ruleslogin);

		if ($validation->passes()) {

			if (Auth::attempt($data, Input::get('rememberme'))){

					//creating package if it doesnt have package				
					$user = Auth::user();

					// if user not member/submember
					if($user->user_role !== 'member' || $user->user_role !== 'submember'){

						$response = Response::json(array('status' => 0,'data' => array(),'message' => 'Username/Password did not match','alert'=>'alert-warning'));

					}

					// if user !aktif
					if($user->user_status !== 'aktif'){

						switch ($user->user_status) 
						{
							case 'nonaktif':
								$message = 'Your account is not activated yet. Check your email for confirmation code or resend if necessary';
								$message_type = 'not_active';
								break;
							case 'deleted':
								$message = 'Your account is deleted. You can register to join Vendpad again';
								$message_type = 'deleted';
								break;
							default:
								$message = 'Your account is not activated yet. Check your email for confirmation code or resend if necessary';
								$message_type = 'not_active';
								break;
						}
						
						return Response::json(array('status' => 0,'message' => $message,'message_type'=>$message_type,'alert'=>'alert-warning'));

					}

					$token = UserController::generateUserToken($user->user_id);

					if($token == false){

						return Response::json(array('status' => 0,'message' => 'User not found','message_type'=>'user_not_found','alert'=>'alert-warning'));

					}

                	$response = Response::json(array('status' => 1,'data' => UserController::getData($user),'message' => 'Logging you in','message_type'=>'login_success','token' => $token->token_data,'alert'=>'alert-success'));

	        }else{

		        $response = Response::json(array('status' => 0,'data' => array(),'message' => 'Username/Password did not match','message_type'=>'username_password_not_match','alert'=>'alert-warning'));

	        }

	        return $response;	   

		}

        //return $validation->messages()->toJson();
        return $response = Response::json(
                array(
                    'data' => array(),
                    'status' => 0,
                    'message' => 'Please check for empty field or wrong format',
                    'message_type'=>'empty_field',
                    'alert'=>'alert-warning',
                    'errors' => HomeController::getErrors($validation->errors()->getMessages())
                    )
                );
    }

	/**
	* Convert error messagess to array
	*
	* @param array
	* @return array
	*
	**/
	public static function getErrors($error){

		$errors = array();

		foreach ($error as $e) {

			$errors[] = array(
				
				'data' => $e[0]

				);					

		}

		return $errors;
	}

}