<?php

class MessageController extends \BaseController {

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index($id)
	{
		$id = Crypt::decrypt($id);

		$user = Tbl_user::find($id);

		if(is_object($user)){

			$convs_from = $user->conversationfrom()->orderBy('updated_at','desc')->get();
			$convs_to = $user->conversationto()->orderBy('updated_at','desc')->get();

			$data = array();

			foreach($convs_from as $c){

				$data[] = self::data($c,$id);

			}
			foreach($convs_to as $c){

				$data[] = self::data($c,$id);

			}

			return Response::json(array('status' => 1, 'aaData' => $data, 'message' => 'Succesfully'));
		}

		return Response::json(array('status' => 0, 'aaData' => array(), 'message' => 'User not found'));
	}

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function indexMessage($id,$conv){

		$id = Crypt::decrypt($id);

		$user = Tbl_user::find($id);

		$conv = self::getConv($user,$conv);

		if(is_object($user) && is_object($conv)){

			$data = self::data($conv,$id);

			return Response::json(array('status' => 1, 'data' => $data, 'message' => 'Data Found'));

		}

		return Response::json(array('status' => 0, 'data' => array(), 'message' => 'Data not found'));
	}

	public static function getConv($user,$id){

		$conv1 = $user->conversationfrom()->where('mc_id',$id)->first();
		$conv2 = $user->conversationto()->where('mc_id',$id)->first();

		if(is_object($conv1)){
			return $conv1;
		}else if(is_object($conv2)){
			return $conv2;
		}

		return false;
	}

	/**
	* Atur ulang array conversation
	* @param object dari conversation
	* @return object
	**/
	public static function data($c,$user){

		$messages = $c->messages;
		$unread = 0;
		$last = 'No Chat yet';

		$m = array();
		foreach($messages as $mes){
			
			$m[] = self::dataMessage($mes);

			if($mes->message_user_id !== $user && $mes->message_status == 'unread') $unread++;
			$last = $mes->message_body;

		}

		// switching
		$from = $c->from;
		$to = $c->to;
		$tmp = null;

		if($user !== $from->user_id){
			$tmp = $from;
			$from = $to;
			$to = $tmp;
		}

		$data = array(

			'from' => UserController::getData($from),
			'to' => UserController::getData($to),
			'id' => $c->mc_id,
			'unread' => $unread,
			'last_message' => $last,
			'created_at' => strtotime($c->created_at),
			'updated_at' => strtotime($c->updated_at),
			'messages' => $m

			);

		return $data;
	}
	/**
	* Atur ulang array message
	* @param object dari message
	* @return object
	**/
	public static function dataMessage($m,$token = 0){

		$user = $m->user;
		$data = array(

			'body' => $m->message_body,
			'token' => $token,
			'type' => $m->message_type,
			'created_at' => $m->created_at,
			'updated_at' => $m->updated_at,
			'status' => 'sent',
			'user' => UserController::getData($user),
			'id' => $m->message_id

			);

		return $data;
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function store($from,$to)
	{
		$input = array(

		    'mc_user_one' => Crypt::decrypt($from),
		    'mc_user_two' => Crypt::decrypt($to)

			);

		$validation = Validator::make($input,Tbl_message_conversation::$rules);

		if($validation->passes()){

			if(self::checkChatDuplicate(Crypt::decrypt($from),Crypt::decrypt($to)))
				return Response::json(array('status' => 0,'data' => array(),'type' => 'Operation Warning','message' => 'You already had chat with this person','alert'=>'alert-success'));

			$data = Tbl_message_conversation::create($input);
			$data = self::data($data,Crypt::decrypt($from));

			return Response::json(array('status' => 1,'data' => $data,'type' => 'Operation Success','message' => 'Conversation stored successfully','alert'=>'alert-success'));

		}

		return 	Response::json(
					array(
						'data' => array(),'status' => 0,
						'message' => 'Please check for empty field or wrong format',
						'alert'=>'alert-warning',
						'errors' => HomeController::getErrors($validation->errors()->getMessages())
						)
					);
	}

	/**
	*
	* Store check apakah sudah pernah chat atau belum
	* @param user 1
	* @param user 2
	**/
	public static function checkChatDuplicate($user1,$user2){

		// user one
		$one = false;$two = false;
		$data = Tbl_message_conversation::where('mc_user_one',$user1)->where('mc_user_two',$user2)->first();
		if(is_object($data)) $one = true;

		// user two
		$data = Tbl_message_conversation::where('mc_user_two',$user1)->where('mc_user_one',$user2)->first();
		if(is_object($data)) $two = true;

		return $one == true || $two == true ? true : false;
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function storeMessage($id,$from)
	{

		$token = Input::get('token');
		$input = array(

			'message_body' => Input::get('body'),
			'message_c_id' => $id,
			'message_status' => 'unread',
			'message_user_id' => Crypt::decrypt($from),
			'message_type' => Input::get('type')

			);

		$validation = Validator::make($input,Tbl_message::$rules);

		if($validation->passes()){

			$data = Tbl_message::create($input);
			$data = self::dataMessage($data,$token);

			return Response::json(array('status' => 1,'data' => $data,'type' => 'Operation Success','message' => 'Message stored successfully','alert'=>'alert-success'));

		}

		return 	Response::json(
					array(
						'data' => array(),'status' => 0,
						'message' => 'Please check for empty field or wrong format',
						'alert'=>'alert-warning',
						'errors' => HomeController::getErrors($validation->errors()->getMessages())
						)
					);
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show($id)
	{
		//
	}


	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function edit($id)
	{
		//
	}


	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update($id)
	{
		//
	}


	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy($id,$conv)
	{
		$id = Crypt::decrypt($id);

		$user = Tbl_user::find($id);

		$conv = $user->conversationfrom()->where('mc_id',$conv)->first();

		if(is_object($user) && is_object($conv)){

			$mes = $conv->messages;
			foreach ($mes as $m) {
				$m->delete();
			}

			$conv->delete();

			$data = self::data($conv,$id);
			return Response::json(array('status' => 1,'data' => $data,'type' => 'Operation Success','message' => 'Conversation deleted successfully','alert'=>'alert-success'));

		}

		return Response::json(array('status' => 0,'data' => array(),'type' => 'Operation Success','message' => 'You cant delete a conversation that does not belong to you','alert'=>'alert-success'));

	}


}
