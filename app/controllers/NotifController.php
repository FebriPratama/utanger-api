<?php

class NotifController extends \BaseController {

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{
		
		$result = Tbl_notification_type::all();
				
		$rst = array();

		foreach ($result as $value) {

			$rst[] = array(
					'body' => $value->notif_type_body,
					'name' => $value->notif_type_name,
					'opt' => '<div class="btn-group">
                            <button type="button" class="btn btn-default btn-flat dropdown-toggle" data-toggle="dropdown" aria-expanded="true">
                              <span class="fa fa-cog"></span>
                              <span class="sr-only">Toggle Dropdown</span>
                            </button>
                            <ul class="dropdown-menu" role="menu">
                              <li><a href="'.URL::to('modal/notif/'.$value->notif_type_id).'/edit" data-toggle="modal" data-target="#modal-general" >Edit</a></li>
                              <li><a href="'.URL::to('modal/notif/'.$value->notif_type_id).'/delete" data-toggle="modal" data-target="#modal-general" >Delete</a></li>
                            </ul>
                          </div>'
					); 

		}

		return $rst = array('aaData' => $rst);
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store()
	{		

		$input = array(

			'notif_type_body' => Input::get('body'),
			'notif_type_name' => BaseController::to_prety_url(Input::get('name'))

			);

		$validation = Validator::make($input,Tbl_notification_type::$rules);

		if($validation->passes()){

			$product = Tbl_notification_type::create($input);		

			return Response::json(array(array('status' => '1','message' => 'Notification type created successfully','alert'=>'alert-success')));
		}

		return $validation->messages()->toJson();
	}


	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */

	public function update($id)
	{

		$name = BaseController::to_prety_url(Input::get('name'));

		$input = array(
			'notif_type_body' => Input::get('body'),
			'notif_type_name' => $name
			);

		$validation = Validator::make($input,Tbl_notification_type::$rulesupdate);

		if($validation->passes()){

			$notif = Tbl_notification_type::find($id);

			if($notif->notif_type_name !== $name){

				$exist = Tbl_notification_type::where('notif_type_name',$name)->first();

				if(is_object($exist)){
					
					return Response::json(array(array('status' => '0','message' => 'Duplicated notification type name','alert'=>'alert-success')));
				}
			}

			$notif->update($input);	

			return Response::json(array(array('status' => '1','message' => 'Notification Type updated successfully','alert'=>'alert-success')));
		}

		return $validation->messages()->toJson();
	}

	public function delete($id)
	{

		Tbl_notification_type::find($id)->delete();

		return Response::json(array(array('status' => '1','message' => 'Notification Type successfuly deleted','alert'=>'alert-success')));
	}

	/**
	* Store notif if not exist
	* @param $type
	* @return object
	**/
	public static function notExistNotif($type){

		$data = array();

		switch ($type) {
			case 'product':
				
				$data = array(

				    'notif_type_body' => 'Your team :name just inserted product',
				    'notif_type_name' => 'product'

					);

				break;
			case 'customer':
				
				$data = array(

				    'notif_type_body' => 'Your team :name just inserted customer',
				    'notif_type_name' => 'customer'
					
					);

				break;
			case 'sales':
				
				$data = array(

				    'notif_type_body' => 'Your team :name just inserted sales invoice',
				    'notif_type_name' => 'sales'
					
					);

				break;
			default:

				$data = array(

					'notif_type_body' => 'Something just happened',
					'notif_type_name' => 'something'
					
					);

				break;
		}

		$input = array(
			
			'notif_type_body' => $data['notif_type_body'],
			'notif_type_name' => $data['notif_type_name']

			);

		$validation = Validator::make($input,Tbl_notification_type::$rules);

		if($validation->passes()){

			return Tbl_notification_type::create($input);

		}

		return false;
	}

	/**
	* Get Notification Type
	* @param notif type
	* @param user id
	* @param array fk ( id, type)
	* @return notif 
	* catatan :
	* store butuh type,id user, fk id, fk type,
	* known type { team, system }
	**/
	public static function storeNotif($type,$id,array $foreign){

		$type = Tbl_notification_type::where('notif_type_name',$type)
										->first();

		if(is_object($type)) {

			$input = array(

			    'notif_status' => 'unread',
			    'notif_user_id' => $id,
			    'notif_type_id' => $type->notif_type_id,
			    'notif_fk_id' => $foreign['fk_id'],
			    'notif_fk_type'=> $foreign['fk_type']

				);

			return Tbl_notification::create($input);

		}
		
	}
	
	/**
	 * Update See notifications
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function updateNotifSeen($id){

		$id = Crypt::decrypt($id);

		$user = Tbl_user::find($id);

		if(is_object($user)){

			$input = array(
				
				'notif_status' => 'read'

				);

			foreach($user->notifications as $notif){

				$notif->update($input);

			}

			return Response::json(array('status' => 1,'message' => 'Updated','alert'=>'alert-success'));

		}

		return Response::json(array('status' => 0,'message' => 'User Not Found','alert'=>'alert-success'));
	}
	/**
	 * See notifications
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function getNotifUserIndex($id){

		$id = Crypt::decrypt($id);

		$user = Tbl_user::find($id);

		if(is_object($user)){

			$rst = array();
			$tmp = array('date' => '', 'data' => array());

			$i = 0;

			$notifications = $user->notifications()->orderBy('notif_id','DESC')->paginate(5);

			foreach($notifications as $notif){

				$nt = $notif->type;
				$type = array('fk_id' => $notif->notif_fk_id, 'fk_type' => $notif->notif_fk_type);
				$body = self::generateNotifBody($nt->notif_type_body,$user,$type);

				//group by fk id and type and add number
				if($tmp['date'] == date('Y/m/d',strtotime($notif->created_at))) {

					$check = array('status' => false, 'index' => 0);
					$j=0;

					// shorten the rst by index
					$rstTmp = $rst[$tmp['index']];

					// mencari data yang sama dari data $rst sehingga dapat digroup
					foreach($rstTmp['data'] as $data){
						
						if(Crypt::decrypt($data['fk']) == $notif->notif_fk_id && $data['type'] == $notif->notif_type_id){

							$check['status'] = true;
							$check['index'] = $j;

						}

						$j++;
					}

					// Jika ketemu maka diupdate totalnya
					if($check['status']){

						$update = array('total' => $rstTmp['data'][$check['index']]['total'] + 1,'body' => $body);

						$rst[$tmp['index']]['data'][$check['index']]['total'] = $update['total'];

						$rst[$tmp['index']]['data'][$check['index']]['body'] = $update['body'].'('.$update['total'].')';
						
						$rst[$tmp['index']]['data'][$check['index']]['created_at'] = $notif->created_at;

					}
					// if false insert to data
					else{

						$rst[$tmp['index']]['data'][] = array(

							'id' => Crypt::encrypt($notif->notif_id),
							'body' => $body,
							'total' => 1,
							'fk' => Crypt::encrypt($notif->notif_fk_id),
							'type' => $notif->notif_type_id,
							'status' => $notif->notif_status,
							'created_at' => $notif->created_at

							);

					}

				}
				// create new tmp
				else{

					$data = array(
						'id' => Crypt::encrypt($notif->notif_id),
						'body' => $body,
						'total' => 1,
						'fk' => Crypt::encrypt($notif->notif_fk_id),
						'type' => $notif->notif_type_id,
						'status' => $notif->notif_status,
						'created_at' => $notif->created_at
						);

					$rst[] = array(

						'date' => date('Y/m/d',strtotime($notif->created_at)),
						'data' => array($data)

						);

					$tmp['date'] = date('Y/m/d',strtotime($notif->created_at));
					$tmp['index'] = $i;

				}

			}	

			return Response::json(array('status' => 1,'message' => 'Success', 'data' => $rst,'new' => $user->notifications()->where('notif_status','unread')->count(),'alert'=>'alert-success'));
		
		}

		return Response::json(array('status' => 0,'message' => 'User Not Found','alert'=>'alert-success'));
	
	}

	/**
	 * See notifications
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function getNotifUser($id){

		$id = Crypt::decrypt($id);

		$user = Tbl_user::find($id);

		if(is_object($user)){

			$rst = array();

			$tmp = array('index' => 0,'body' => '','type' => 0,'fk' => 0,'fk_type'=>'','total' => 0);

			$i = 0;

			$notifications = $user->notifications()->paginate(5);

			foreach($notifications as $notif){

				$nt = $notif->type;

				//group by fk id and type and add number
				if($tmp['fk'] == $notif->notif_fk_id && $tmp['type'] == $notif->notif_type_id){

					$tmp['total'] = $tmp['total'] + 1;

					$rst[$tmp['index']]['body'] = $tmp['body'].'('.$tmp['total'].')';

				}
				// create new tmp
				else{

					// type body,user,fk_id,fk_type 
					$type = array('fk_id' => $notif->notif_fk_id, 'fk_type' => $notif->notif_fk_type);
					$body = self::generateNotifBody($nt->notif_type_body,$user,$type);

					$rst[]=array(

						'id' => Crypt::encrypt($notif->notif_id),
						'body' => $body,
						'status' => $notif->notif_status,
						'created_at' => $notif->created_at

						);


					$tmp = array(
						
						'index' => $i,
						'body' => $body,
						'type' => $notif->notif_type_id,
						'total' => 1,
						'fk' => $notif->notif_fk_id,
						'fk_type'=> $notif->notif_fk_type

						);

				}

			}	

			return Response::json(array('status' => 1,'message' => 'Success', 'data' => $rst,'new' => $user->notifications()->where('notif_status','unread')->count(),'alert'=>'alert-success'));
		
		}

		return Response::json(array('status' => 0,'message' => 'User Not Found','alert'=>'alert-success'));
	
	}

	/**
	* Replace body from notif type to human readable
	* @param body text
	* @param object user
	* @param fk array
	* @return string
	**/
	public static function generateNotifBody($text,$user,array $type){


		switch ($type['fk_type']) {
			case 'team':

				$team = Tbl_user::find($type['fk_id']);
				$team = is_object($team) ? $team->user_fullname : 'Unknown';

				$text = str_replace(array(':from',':name'), array('Your',$team), $text);

				break;
			case 'system':

				$text = str_replace(':from', 'You', $text);

				break;
			default:
				# code...
				break;
		}

		return $text;
	}

	/* 
		old codes

				
		$nt = $notif->type;

		if($tmp_type['type'] == $notif->notif_type_id){

			$tmp_type['total'] = $tmp_type['total'] + 1;

			$rst[$tmp_type['index']]['body'] = $tmp_type['body'].'('.$tmp_type['total'].')';

		}else{

			$rst[]=array(

				'id' => Crypt::encrypt($notif->notif_id),
				'body' => $user->user_fullname.' '.$nt->notif_type_body,
				'status' => $notif->notif_status,
				'created_at' => $notif->created_at

				);

			$tmp_type = array(
				
				'index' => $i,
				'body' => $user->user_fullname.' '.$nt->notif_type_body,
				'type' => $notif->notif_type_id,
				'total' => 1

				);

		}		

		$i++;

	*/
}
