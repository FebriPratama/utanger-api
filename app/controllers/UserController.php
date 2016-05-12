<?php

class UserController extends \BaseController {

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{
		$users = Tbl_user::all();

		return Response::json($users);
	}

	public function adminGet(){

		$result = Tbl_user::where('user_role','member')->get();

		$rst = array();
		
		$pre = date('Y-m-d');

		foreach ($result as $value) {

            $package = $value->package;

            $img = 'img/user/profile/'.$value->user_img_profile;

            if(!File::exists(public_path($img))){
                
                $img = URL::asset('img/user/profile/avatar5.png');

            }

			$rst[] = array(
					'name' => $value->user_fullname,
                    'status' => $value->user_status,
                    'package' => $package->package_name,
					'permalink' => '<a href="'.URL::to('profile/'.$value->user_permalink).'" target="_BLANK" >'.$value->user_permalink.'</a>',
					'email' => $value->email,
                    'picture' => URL::asset($img),
					'opt' => '<div class="btn-group">
                            <button type="button" class="btn btn-default btn-flat dropdown-toggle" data-toggle="dropdown" aria-expanded="true">
                              <span class="fa fa-cog"></span>
                              <span class="sr-only">Toggle Dropdown</span>
                            </button>
                            <ul class="dropdown-menu" role="menu">
                              <li><a href="'.URL::to('modal/member/'.$value->user_id).'/edit" data-toggle="modal" data-target="#modal-general" >Edit</a></li>
                              <li><a href="'.URL::to('modal/member/'.$value->user_id).'/slide" data-toggle="modal" data-target="#modal-general" >Slide</a></li>
                              <li><a href="'.URL::to('modal/member/'.$value->user_id).'/package" data-toggle="modal" data-target="#modal-general" >Package&Payment</a></li>
                              <li><a href="'.URL::to('admin'.$pre.'/member/'.$value->user_id).'/interpersonate" target="_BLANK" >Login</a></li>
                              <li><a href="'.URL::to('modal/member/'.$value->user_id).'/delete" data-toggle="modal" data-target="#modal-general" >Delete</a></li>
                            </ul>
                          </div>
                          <!--<a href="'.URL::to('admin'.$pre.'/member/'.$value->user_id).'/products"  data-toggle="tooltip" data-placement="right" title="User Products" class="btn btn-info btn-flat" ><i class="ion-ios-cart-outline"></i></a>
                          <a href="'.URL::to('admin'.$pre.'/member/'.$value->user_id).'/customers"  data-toggle="tooltip" data-placement="right" title="User Customers" class="btn btn-warning btn-flat" ><i class="ion-ios-people-outline"></i></a>-->
                          '
					); 

		}

		return $rst = array('aaData' => $rst);
	}

    public function notif($id){

        if(Auth::user()->check()){

            $id = Auth::user()->get()->user_id;

            $input = array(
                
                'notif_status' => 'read'

                );

            $notif = Tbl_notification::where('notif_user_id',$id)->where('notif_status','unread')->get();

            foreach($notif as $no){
                
                $no->update($input);

            }

            $unread = DB::table('tbl_notifications')
                            ->join('tbl_notification_types','tbl_notification_types.notif_type_id','=','tbl_notifications.notif_type_id')
                            ->where('tbl_notifications.notif_user_id',$id)
                            ->select('tbl_notifications.*','tbl_notification_types.*')->get();

            return Response::json($unread);
        }

        return Response::json(array(array('status' => '1','message' => 'sehat gan ?','alert'=>'alert-success')));
    }

    public function getNotif($id){

        if(Auth::user()->check()){

            $id = Auth::user()->get()->user_id;

        }

        $unread = DB::table('tbl_notifications')
                        ->join('tbl_notification_types','tbl_notification_types.notif_type_id','=','tbl_notifications.notif_type_id')
                        ->where('tbl_notifications.notif_user_id',$id)
                        ->where('tbl_notifications.notif_status','unread')
                        ->select('tbl_notifications.*','tbl_notification_types.*')->get();

        return Response::json($unread);

    }

    /**
    * Digunakan untuk melakukan check user email
    * saat register, karena jika user deleted
    * akan daftar ulang maka tinggal diupdate status
    * menjadi aktif
    * @param email
    * @return boolean
    * 
    **/
    public static function checkUserEmailExist($email){
        
        $user = Tbl_user::where('email',$email)
                            ->where('user_role','member')
                            ->where('user_status','deleted')
                            ->first();
        
        if(is_object($user)){

            return true;

        }

        return false;
    }

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store()
	{

            $address = Input::get('address');
            $birthdate =date('Y-m-d');
            $desc = 'This is your description';

            // check email
            $rules = self::checkUserEmailExist(Input::get('email')) ? Tbl_user::$rulesuserexist : Tbl_user::$rulesbiasa;

            $input = array(

                        'user_fullname' => Input::get('name'),
                        'user_img_profile' => 'avatar5.png',
                        'user_description' => $desc,
                        'user_birthdate' => $birthdate,
                        'password' => Input::get('pswd'),
                        'password_confirmation' => Input::get('password'),
                        //'user_address' => $address,
                        'user_status' => 'nonaktif',
                        'user_role' => 'member',
                        'user_phone_number' => Input::get('phone') == null ? '0896' : Input::get('phone'),
                        'email' => Input::get('email')
                        
                    );

      		//Checking for empty variables
            $validation = Validator::make($input, $rules);

            if($validation->passes())
            {
                // remove password_confirmation field
                $input['password'] = Hash::make(Input::get('pswd'));
                unset($input['password_confirmation']);

            	// Jika user pernah daftar dan status deleted maka akan update saja
                if(self::checkUserEmailExist(Input::get('email'))){

                    $user = Tbl_user::where('email',Input::get('email'))->first();

                    $user->update($input);

                }else{

                    $user = Tbl_user::create($input);

                }                

                $userid = $user->user_id;    

                //Creting hash kode
                $kode = self::generateConfirmCode();

                //Creating validation data
                $user->update(array('user_validation_code'=>$kode));
                
                //Kirim Email verification
                $sendmail = Mail::send('emails.sendgrid-user-validation', array('nama'=> Input::get('name'),'kode'=> $kode), function($message)
                      {

                         $message->to(Input::get('email'), Input::get('name'))->subject('E-mail Verification');

                      });

                return Response::json(array('status' => 1,'message' => 'Please check your email, We have sent you a verification code. Check spam folder if in your inbox doesnt appear in 15 mins','alert'=>'alert-success'));

            }

            //return Response::json(array(array('status' => '0','message' => 'user creating failed','alert'=>'alert-danger')));
            //return $validation->messages()->toJson();
            return $response = Response::json(
                array(
                    'data' => array(),'status' => 0,
                    'message' => 'Please check for empty field or wrong format',
                    'alert'=>'alert-warning',
                    'errors' => HomeController::getErrors($validation->errors()->getMessages())
                    )
                );
	}

    /**
    * Resend validation code
    * 
    **/
    public function resendConfirmCode(){

        $email = Input::get('email');

        $user = Tbl_user::where('email',$email)->first();

        if(is_object($user)){

            $sendmail = Mail::send('emails.uservalidation', array('nama'=> $user->user_fullname,'kode'=> $user->user_validation_code), function($message) use($user)
                  {

                     $message->to($user->email,$user->user_fullname)->subject('E-mail Verification');

                  });

            return Response::json(array('status' => 1,'data' => array(),'message' => 'Confirm code has been sent to '.$user->email.' .Check spam folder if in your inbox doesnt appear in 15 mins','alert'=>'alert-success'));

        }

        return Response::json(array('status' => 0,'data' => array(),'message' => 'Your e-mail is not registered to Vendpad','alert'=>'alert-success'));

    }

    /**
    * COnfirm validation code
    * 
    **/
    public function validationConfirmCode(){

        $code = Input::get('code');

        $user = Tbl_user::where('user_validation_code',$code)->first();

        if(is_object($user)){

            $input = array(
                
                'user_status' => 'aktif'

                );

            if($user->user_status != 'nonaktif'){

                return Response::json(array('status' => 0,'data' => array(),'message' => 'You have validated your account.','alert'=>'alert-success'));

            }

            $user->update($input);

            return Response::json(array('status' => 1,'data' => array(),'message' => 'Thank you for activating your account. You can procced to login page. Welcome to vendpad !','alert'=>'alert-success'));

        }

        return Response::json(array('status' => 0,'data' => array(),'message' => 'You have entered wrong validation code.','alert'=>'alert-success'));

    }

    /**
    * Generate random confirmation code rekursif
    *
    *
    **/

    public static function generateConfirmCode(){

        $codenew = self::checkCodeConfirmUser(sprintf('%06X', mt_rand(0, 16777215)));

        return $codenew;

    }

    public static function checkCodeConfirmUser($code){

        $code = trim($code) == '' ? self::generateConfirmCode() : $code;

        $user = Tbl_user::where('user_validation_code',$code)->first();

        if(is_object($user)){

            // generate new code
            $code = self::generateConfirmCode();

        }

        return $code;
    }

    /**
    * Update user package from page billing
    * @param id user
    * @return json
    **/
    public function updatePackage($id){

        $id = Crypt::decrypt($id);

        $user = Tbl_user::find($id);

        if(is_object($user)){

            // check current package
            $package = $user->package;

            if($package->package_name !== 'Paid'){

                // update payment and user package
                $input = array('payment_status'=>'waiting');

                $payment = $user->payment;

                $payment->update($input);

                    $paid = Tbl_package::where('package_name','Paid')->first();
                    $paid = is_object($paid) ? $paid->package_id : 2;

                    // set package id and expire 360 days
                    $input = array(
                        
                        'user_package_id' => $paid,
                        'user_expire_date' => date('Y-m-d H:i:s', strtotime('+30 day', strtotime(date('Y-m-d H:i:s'))))

                        );

                    $user->update($input);

                    /* insert notif type,id user, array(id fk,type fk) */
                    $foreign = array(

                        'fk_id' => 1,
                        'fk_type' => 'system'

                        );

                    NotifController::storeNotif('payment',$user->user_id,$foreign);  

                // voucher code
                if(trim(Input::get('code')) !== ''){
                    $claims = self::voucherUsedStore(Input::get('code'),$user->user_id);
                }

                return Response::json(array('status' => 1,'data' => array(),'message' => 'You have upgraded to Paid Plan. Finish payment by transfer Rp. 600.000,00 to 123-456-786. Then send email confirmation & attach payment proof to sales@vendpad.com.','alert'=>'alert-success'));
            }

            return Response::json(array('status' => 0,'data' => array(),'message' => 'You already in Paid Plain','alert'=>'alert-success'));
        }

        return Response::json(array('status' => 0,'data' => array(),'message' => 'User not found','alert'=>'alert-success'));

    }

    /**
    * Mofiy package feature value dari modal admin member
    *
    * @param user id
    * @return object
    **/

    public function updatePackageFeature($id){

        // get user with id
        $user = Tbl_user::find($id);

        if(!is_object($user)) return Response::json(array(array('status' => '0','message' => 'Data Not Found','alert'=>'alert-warning')));
        
        $features = $user->features;

        foreach($features as $f){

            // semua di get terus disamaain dengan input get
            $feat = $f->feature;

            // get input name
            $name = BaseController::to_prety_url($feat->feature_name);

            if(trim(Input::get($name)) !== ''){

                $input = array(

                    'fv_value' => Input::get($name)

                    ); 

                $f->update($input);   
                             
            }

        }

        // update user package
        if(trim(Input::get('package')) !== ''){

            $input = array('user_package_id'=>Input::get('package'));
            $user->update($input);

        }

        // update user payment
        if(trim(Input::get('payment')) !== ''){
            
            $payment = $user->payment;

            // notify user if before waiting
            if(Input::get('payment') == 'done' && $payment->payment_status == 'waiting'){

                $foreign = array(

                    'fk_id' => 1,
                    'fk_type' => 'system'

                    );

                NotifController::storeNotif('confirm_package',$user->user_id,$foreign);

            }

            $input = array('payment_status'=>Input::get('payment'));
            $payment->update($input);

        }

        return Response::json(array(array('status' => '1','message' => 'Feature successfuly updated','alert'=>'alert-success')));
    }


    /**
    * Sedang dalam maintenis
    * ----------------------
    * saat user sbg free langsung aktif dan create feature
    * jk paid tunggu payment done baru paket aktif
    * @param user object
    * @param package id
    * @param voucher boolean
    * @param force boolean
    * @return boolean
    **/
    public static function creatingPackage($user,$package = null, $voucher = false,$force = false){

        // find package
        $package = Tbl_package::find($package);

        $package = is_object($package) ? $package : Tbl_package::where('package_name','Free')->first();

        // payment only happened once for records
        $payment = Tbl_payment::where('payment_user_id',$user->user_id)->first();

        //hanya jika jika payment belum ada
        if( !is_object($payment) || $force == true){

                //create payment
                $input = array(

                    'payment_user_id' => $user->user_id,
                    'payment_package_id' => $package->package_id,
                    'payment_date' => date('Y-m-d H:i:s'),
                    'payment_status' => $package->package_name == 'Paid' ? 'waiting' : 'done',
                    'payment_description' => 'No Description'

                    );

                $pay = Tbl_payment::create($input);

                // update user package id here
                $input = array(
                    'user_package_id' => $package->package_id
                    );

                $user->update($input);

                /* insert notif */
                if($package->package_name == 'Paid'){

                    $foreign = array(

                        'fk_id' => 1,
                        'fk_type' => 'system'

                        );

                    NotifController::storeNotif('payment',$user->user_id,$foreign);
                      
                }

                // features, if paid we use free cz payment still waiting
                self::creatingUserFeature($user,$package->package_name == 'Paid' ? true : false);

                return true;
        }

        return true;
            
    } 

    /**
    * Update user voucher owner expiration
    * Store voucher used history
    * @param code
    * @param id
    *
    * @return boolean
    **/
    public static function voucherUsedStore($code,$id){

        $code = Tbl_voucher::where('voucher_code',$code)->first();

        if(!is_object($code)) return false;

        // user voucher owner
        $user = $code->user;

        // check user id for duplicated voucher claimation
        if(self::isVoucherClaimed($id,$code->voucher_id)) return false;

        // store voucher used
        $input = array(

            'voucher_used_user_id' => $id,
            'voucher_used_date' => date('Y-m-d H:i:s'),
            'voucher_used_voucher_id' => $code->voucher_id

            );

        Tbl_voucher_used_history::create($input);

        // user voucher owner
        // extend user expriation by 30 days
        $input = array(

            'user_expire_date' => date('Y-m-d H:i:s', strtotime('+30 day', strtotime($user->user_expire_date)))

            );
        
        $user->update($input);

        // user voucher claimer
        // extends user expiration by 30 days
        $user = Tbl_user::find($id);

        $input = array(

            'user_expire_date' => date('Y-m-d H:i:s', strtotime('+30 day', strtotime($user->user_expire_date)))

            );
        
        $user->update($input);

        return true;
    }
    
    /**
    * Check is user already claimed the voucher
    * @param user id
    * @param voucher id
    * @return boolean
    **/
    public static function isVoucherClaimed($user,$voucher){

        $history = Tbl_voucher_used_history::where('voucher_used_user_id',$user)
                                                ->where('voucher_used_voucher_id',$voucher)->first();

        return is_object($history) ? true : false;

    }

    /* old code 
    public static function creatingPackage($user,$package = null, $force = false){

        // features
        self::creatingUserFeature($user);

        return true;

        $package = Tbl_package::where('package_name',$package)->first();

        $free = Tbl_package::where('package_name','Free')->first();

        $payment = Tbl_payment::where('payment_brand_id',$user->user_id)->get();

        //hanya jika jika payment belum ada
        if(count($payment) < 1 || $force == true){

            if(is_object($package)){

                $date = date('Y-m-d H:i:s', strtotime('+'.(int)$package->package_expire_duration.' day', strtotime(date('Y-m-d H:i:s'))));

                //create payment
                $input = array(
                    'payment_user_id' => $user->user_id,
                    'payment_package_id' =>$package->package_id,
                    'payment_date' => $date,
                    'payment_status' => 'done',
                    'payment_description' => 'main'
                    );

                $pay = Tbl_payment::create($input);

                return true;

            }else if(is_object($package) == false && is_object($free) == true){

                $date = date('Y-m-d H:i:s', strtotime('+'.(int)$free->package_expire_duration.' day', strtotime(date('Y-m-d H:i:s'))));
                
                //create payment
                $input = array(

                    'payment_user_id' => $user->user_id,
                    'payment_package_id' =>$free->package_id,
                    'payment_date' =>$date,
                    'payment_status' => 'done',
                    'payment_description' => 'main'
                    
                    );

                $pay = Tbl_payment::create($input);

                return true;

            }else if(is_object($package) == false && is_object($free) == false){

                $input = array(
                            'package_name' => 'Free',
                            'package_price'=> 0,
                            'package_expire_duration'   => '365'
                            );

                $package = Tbl_package::create($input);

                $date = date('Y-m-d H:i:s', strtotime('+'.(int)$package->package_expire_duration.' day', strtotime(date('Y-m-d H:i:s'))));

                //create payment
                $input = array(
                    'payment_user_id' => $user->user_id,
                    'payment_package_id' =>$package->package_id,
                    'payment_date' =>$date,
                    'payment_status' => 'done',
                    'payment_description' => 'main'
                    );

                $pay = Tbl_payment::create($input);

                return true;

            }

            return false;
        }

        return true;
            
    } */

    /**
    * Check payment for paid package 
    * jika payment sudah done maka user akan mendapatkan paid features
    * @param object user
    * @return boolean
    **/
    public static function checkPayment($user){
        
        $features = $user->features;

        // update to pro if user is pro package and status payment done
        foreach($features as $f){

            // jika
        
        }

    }


    /**
    * Creating relation between user and feature
    * @param user object
    * @return boolean
    *
    **/
    public static function creatingUserFeature($user,$hold = false){

        $features = Tbl_feature::all();
        $package = $user->package;

        foreach($features as $f){

            // check for duplicate feature assign
            if(!self::isFeatureExistInUser($f->feature_id,$user->user_id)){

                $input = array(

                    'fv_user_id' => $user->user_id,
                    'fv_feature_id' => $f->feature_id,
                    'fv_value' => $package->package_name == 'Free' || $hold ? $f->feature_default_free : $f->feature_default_pro

                  );

                Tbl_feature_value::create($input);

            }

        }

        return true;
    }
    
    /**
    * Get user id from spesified feature name
    * @param string name
    * @return object
    *
    **/
    public static function getFeatureByName($name){

        return Tbl_feature::where('feature_name',$name)->first(); 

    }

    /**
    * Creating relation between user and feature
    * @param user id
    * @return boolean
    *
    **/
    public static function isFeatureExistInUser($feature,$user){

        $feature = Tbl_feature_value::where('fv_feature_id',$feature)->where('fv_user_id',$user)->first();

        if(is_object($feature)){

            return true;
        }

        return false;
    }
    /**
    * Generate random permalink
    *
    * Jika ada permalink yang sama atau folder dari permallink exist dia
    * akan generate string permalink unik baru
    *
    * @param $string permalink 
    * @param $id parent_user int
    * @return $string permalink
    *
    **/

    public static function generatePermalink($string, $id = 2){

        $string = strlen($string) <= 3 ? $string.''.rand(10,100) : $string;
        
        $string = BaseController::to_prety_url($string);

        /* check folder and db for existance */        
        $user = Tbl_user::where('user_permalink',$string)->first();
        $path = public_path().'/img/user/' . $string;

        if(is_object($user) || File::exists($path)){

            $string = $string.''.substr(md5($id), 0, 8);

        }

        return $string;
    }

    /**
    * Check permalink
    *
    * Jika ada permalink yang sama atau folder exist
    * maka return false
    *
    * @param $string permalink 
    * @return boolean
    *
    **/
    public static function checkDuplicatePermalink($string){

        $string = BaseController::to_prety_url($string);

        /* check folder and db for existance */        
        $user = Tbl_user::where('user_permalink',$string)->first();
        $path = public_path().'/img/user/' . $string;

        if(is_object($user) || File::exists($path)){

            return false;

        }

        return true;

    }

	public function photoCrop($id){

        $user = Tbl_user::find($id);

            //get crop data                
            $data = explode('-', Input::get('photo-data'));

            $destinationPath = public_path().'/img/user/profile/';

            $filename = $user->user_img_profile;

            //crop picture
            $img = Image::make($destinationPath.''.$filename);

            $img->crop($data[3], $data[2], $data[0], $data[1])->save($destinationPath.''.$filename);

          return Response::json(array(array('status' => '1','code' => 'Successfully')));

	}

	public function photoUpload($id){

        $id = Crypt::decrypt($id);

        $input = array(

            'user_img_profile' => Input::file('photo')

            );

        $rules = array(

            'user_img_profile' => 'required|mimes:jpeg,png,jpg'

            );

        $photo = Tbl_user::find($id);

        $validation = Validator::make($input,$rules);

        $info = array('');

        if($validation->passes())
        {
            $file = Input::file('photo');

            if(!File::exists(public_path().'/img/user/'.$photo->user_permalink)){

                File::makeDirectory(public_path().'/img/user/'.$photo->user_permalink);   

            }

            $destinationPath = public_path().'/img/user/'.$photo->user_permalink.'/profile/';

            if(!File::exists($destinationPath)){

                File::makeDirectory($destinationPath);    

            }

            $destinationPathThumb = public_path().'/img/user/'.$photo->user_permalink.'/profile/thumb/';

            if(!File::exists($destinationPathThumb)){

                File::makeDirectory($destinationPathThumb); 
                        
            }

            if(Input::hasFile('photo'))
            {

                $filename = BaseController::to_prety_url(str_random(6).'_'.$file->getClientOriginalName()).'.'.$file->getClientOriginalExtension();;

                $file->move($destinationPath,$filename);

                $img = Image::make($destinationPath.''.$filename);

                //cek file size 4mb
                if($img->filesize() >= 2048000){

                    $info = array(

                        "name" => "pictures",
                        "size" => 0,
                        "error" => "File size ".$img->filesize()." bytes exceed the maximum allowed of 2048 kb or 2mb"

                    ); 
                    
                    File::delete($destinationPath.$filename);

                    return Response::json(array('files' => $info ));
                }

                //set medium
                $img->resize(400, null, function ($constraint) {
                    $constraint->aspectRatio();
                })->save($destinationPath.''.$filename);

                //set thumb
                $img->resize(100, null, function ($constraint) {
                    $constraint->aspectRatio();
                })->save($destinationPathThumb.''.$filename);

            }else{

                $filename ='avatar5.png';

            }

            $input = array(

                'user_img_profile' => $filename

                );

            //delete old picture that different from the default picture
            if($photo->user_img_profile !=='avatar5.png'){

                $fileold = $photo->user_img_profile;

                File::delete($destinationPath.''.$fileold);

            }

            //update photo
            $photo->update($input);

            $user = UserController::getData($photo);

            $info = array(
                'name' => $filename,
                'data' => $user,
                'size' => Image::make($destinationPath.''.$filename)->filesize(),
                'url' => URL::asset($destinationPath.''.$filename),
                'thumbnailUrl' => URL::asset($destinationPath.''.$filename),
                'deleteUrl' => '#',
                'deleteType' => 'DELETE'
                );

        }else{

            $info = array(

                "name" => "pictures",
                "size" => 0,
                "error" => "Filetype not allowed"

            ); 

        }

        if (isset($_SERVER['HTTP_ACCEPT']) && (strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false)) {

           return Response::json(array('files' => $info ));

        } else {

            return Response::json(array('files' => $info ))->header('Content-Type', 'text/plain');

        }  

	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
    public static function getData($user){

        if(is_object($user)){
                
            $url = URL::asset('/img/user/'.$user->user_permalink.'/profile/'.$user->user_img_profile);

            if ($user->user_img_profile == 'avatar5.png') {

                $url = 'http://dummyimage.com/420x380/2980b9/fff.png&text='.strtoupper($user->user_fullname[0]);

            }

            $user = array(
                
                'user_id' => Crypt::encrypt($user->user_id),
                'user_fullname' => $user->user_fullname,
                'password' => $user->password,
                'email' => $user->email,
                'user_role' => $user->user_role,
                'user_status' => $user->user_status,
                'user_img_profile' => $url,
                'user_phone_number' => $user->user_phone_number,
                'user_birthdate' => $user->user_birthdate,
                'user_status' => $user->user_status,
                'user_address' => $user->user_address,
                'user_description' => $user->user_description,
                'user_validation_code' => $user->user_validation_code,
                'remember_token' => $user->remember_token,
                'updated_at' => $user->updated_at

                );

            return array('status' => 1 ,'user' => $user);

        }
        
        return $user = array();
    }

    /**
    * Get user data
    * @param id user
    * @return array user data
    **/

	public function show($id)
	{

        $user = Tbl_user::find(Crypt::decrypt($id));

        if(is_object($user)){

            //$package= array();
            $package = PackageController::checkUser(Crypt::decrypt($id));
            
            $color = Tbl_color::orderByRaw("RAND()")->first();
                
            $url = URL::asset('/img/user/'.$user->user_permalink.'/profile/'.$user->user_img_profile);

            if ($user->user_img_profile == 'avatar5.png') {

                $url = 'http://dummyimage.com/420x380/'.$color->color_code.'/fff.png&text='.strtoupper($user->user_fullname[0]);

            }
            
            $voucher = $user->voucher;

            $payment = $user->payment;
            
            if(!is_object($payment)){
                // create a free payment with no waiting
                $date = date('Y-m-d H:i:s', strtotime('+360 day', strtotime(date('Y-m-d H:i:s'))));

                $input = array(

                    'payment_user_id' => $user->user_id,
                    'payment_package_id' => $user->user_package_id,
                    'payment_date' => $date,
                    'payment_status' => 'waiting',
                    'payment_description' => 'No Description'

                    );

                $payment = Tbl_payment::create($input);

            }

            $user['user_id'] = Crypt::encrypt($user->user_id);
            $user['user_img_profile'] = $url;
            $user['user_voucher'] = $voucher->voucher_code;
            $user['user_package_id'] = Crypt::encrypt($user->user_package_id);
            $user['user_template_id'] = Crypt::encrypt($user->user_template_id);
            $user['user_key'] = self::generateUserToken(Crypt::decrypt($id))->token_data;

            return Response::json(array('status' => 1 ,'user' => $user,'package' => $package,'payment' => $payment,'token' => $id));

        }
		
        return Response::json(array('status' => 0 ,'user' => array(),'package' => array()));

	}

	public function login(){

	 	$email = Input::get('email') == null ? 'asdsad' : Input::get('email');
	 	$paswd = Input::get('password') == null ? 'asdsad' : Input::get('password');

	 	$login = Tbl_user::where('email','=',$email)->first();

	 	if(is_object($login)){

	 		if(Hash::check($paswd,Tbl_user::find($login->user_id)->password)){
			
				return Response::json(array(array('status' => '1','message' => 'login successfully','alert'=>'alert-success')));	 
	 		
	 		}
			
			return Response::json(array(array('status' => '0','message' => 'login failed, password wrong','alert'=>'alert-success')));					

	 	}

	 	return Response::json(array(array('status' => '0','message' => 'login failed','alert'=>'alert-success')));

	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function edit()
	{	
		return View::make('member.account.profile');
	}

    public function email($id)
    {

        if(Auth::user()->check()){
            $id = Auth::user()->get()->user_id; 
        }

          $input = array(

              'email' => Input::get('email')

              );

          $rule = array(

              'email' => 'required|email|unique:tbl_users,email'

              );

          $validation = Validator::make($input,$rule);

          if($validation->passes())
          {

                // $inputnews = array('newsletter_body' => Input::get('email') );

                // update if subscribe
                $user = Tbl_user::find($id);

                // Tbl_newsletter::where('newsletter_body',Auth::user()->get()->email)->update($inputnews);
                if(Hash::check(Input::get('password'),$user->password))
                {
                
                  $user->update($input);

                  return Response::json(array(array('status' => '1','message' => 'E-mail Updated Succesfuly','alert'=>'alert-success')));

                }

                return Response::json(array(array('status' => '0','message' => 'Wrong password','alert'=>'alert-danger')));    
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

        $rules = Tbl_user::$updateRules;

  		$input = array('user_fullname' => Input::get('name'),
                        'user_phone_number' => Input::get('phone'),
                        'email' => Input::get('email'));

  		//Checking for empty variables
        $validation = Validator::make($input, $rules);

        if ($validation->passes())
        {

        	//Creating user data
            $userid = Tbl_user::find($id)->update($input);

            return Response::json(array(array('status' => '1','message' => 'user updated successfully','alert'=>'alert-success')));

        }

        return Response::json(array(array('status' => '0','message' => 'user updating failed','alert'=>'alert-danger')));

	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function profile($id)
	{

        $id = Crypt::decrypt($id);

        $rules = Tbl_user::$rulesprofile;

        if(Auth::user()->check() && Auth::user()->check()){

        	$id = Auth::user()->get()->user_id;	

        }
        
		$input =  $input = array(

                    'user_fullname' => Input::get('name'),
		            'user_birthdate' => date('Y-m-d',strtotime(Input::get('birthdate'))),
		            'user_address' => Input::get('address'),
		            'user_zip' => Input::get('zip'),
		            'user_phone_number' => Input::get('phone')

                    );

  		//Checking for empty variables
        $validation = Validator::make($input, $rules);

        if ($validation->passes())
        {
        	//Creating user data
            $user = Tbl_user::find($id);

            $user->update($input);
            
            $user = UserController::getData($user);

            return Response::json(array('status' => 1,'data' => $user,'message' => 'user updated successfully','alert'=>'alert-success'));

        }

        return $validation->messages()->toJson();

	}
	
	public function slide($id)
	{

        $rules = Tbl_user::$rulesslide;        

		$input =  $input = array(
		            'user_slide' => Input::get('slide')
		            );

  		//Checking for empty variables
        $validation = Validator::make($input, $rules);

        if ($validation->passes())
        {

        	//Creating user data
            $userid = Tbl_user::find($id)->update($input);

            return Response::json(array(array('status' => '1','message' => 'user updated successfully','alert'=>'alert-success')));

        }

        return $validation->messages()->toJson();

	}
	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function social($id)
	{

        $rules = Tbl_user::$rulessocial;

        $id = Auth::user()->get()->user_id;

		$input =  $input = array(	    
			'user_wa' => Input::get('wa'),
	    	'user_website' => Input::get('website'),
	    	'user_line' => Input::get('line')
	    	);

  		//Checking for empty variables
        $validation = Validator::make($input, $rules);

        if ($validation->passes())
        {

        	//Creating user data
            $userid = Tbl_user::find($id)->update($input);

            return Response::json(array(array('status' => '1','message' => 'user updated successfully, reloading . . .','alert'=>'alert-success')));

        }

        return $validation->messages()->toJson();

	}
	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy($id)
	{

        //user data
        $user = Tbl_user::find($id);

        $user->update(array(
            
            'user_status' => 'deleted'

            ));

        /* delete related
        
            $user->deleteRelation();

            //delete user
            $user->delete();
        
        */

        return Response::json(
            array(
                'status' => '1',
                'message' => 'User deleted successfully',
                'alert'=>'alert-success')
            );

	}

/*
|
|   USER MANAGEMENT
|
*/

	public function adminUpdate($id){

			$title = BaseController::to_prety_url(Input::get('name'));

			$type = Tbl_user::find($id);

			$input = array('user_fullname' => Input::get('name'),
							'user_typeid' => Input::get('type'),
							'user_business_name' => Input::get('name'),
							'user_business_cat'   => Input::get('cat'),
							'password'  => Input::get('pswd'),
							'user_address' => Input::get('address'),
							'user_status' => Input::get('status'),
							'user_zip' => Input::get('zip'),
							'user_phone_number' => Input::get('phone'),
							'email' => Input::get('email'),
							'user_description' => Input::get('desc'),
							'user_website' => Input::get('website'),
							'user_role' => $type->user_role);
			$rules = array(
						'user_fullname' => 'required',
						'user_business_cat' => 'required',
						'user_address' => 'required',
						'user_zip' => 'required',
						'user_phone_number' => 'required',
						'email' => 'required',
						'user_role' => 'required'
					);
			//Checking email for empty variables
				$validation = Validator::make($input, $rules);

				if ($validation->passes()){

					//Creating user data
						Tbl_user::where('user_id','=',$id)->update($input);

						return Redirect::back()->with('message', 'Data '.Input::get('name').' has been updated');
				}

				return Redirect::back()
						->withErrors($validation)
						->with('message', 'There were validation errors.');

	}

    /**
    * Auth bypass for submember and admin
    * @param data user
    * @param data array brand
    * @return boolean
    *
    **/
    public static function authSubMember($brand,$user){

        $check = false;

        /* check kepemilikan admin */
        if ($brand->user_parent_id == $user->user_id) {

            $check = true;

        }else{

            /* memberikan akses ke user submember */
            foreach($brand->bm as $member){

                if($member->bm_user_id == $user->user_id){

                    $check = true;

                }

            }
        
        }

        return $check;
    }

    public function security($id){

            $rules = array(
                    'passwordold' => 'required',
                    'password' => 'required|min:6|confirmed',
                    'password_confirmation' => 'required|min:6'
                );

            $input = Input::all();

            if(Auth::user()->check()){

                $id = Auth::user()->get()->user_id; 

            }

            $user = Tbl_user::find($id);

            if(is_object($user)==false){

                return Response::json(array(array('status' => '0','message' => 'User Not Found','alert'=>'alert-warning')));

            }

            $validation = Validator::make($input,$rules);

            if ($validation->passes()) {

                if(Hash::check(Input::get('passwordold'),$user->password))
                {
                  $input = array(

                      'password' => Hash::make(Input::get('password'))

                    );

                  Tbl_user::where('user_id','=',$id)->update($input);

                  return Response::json(array(array('status' => '1','message' => 'password updated successfully','alert'=>'alert-success')));
                }

                return Response::json(array(array('status' => '0','message' => 'Wrong old Password','alert'=>'alert-warning')));

            }

            return $validation->messages()->toJson();

    }

	public function adminSecurity($id){

			$pswd = Input::get('pswd');

			if(Auth::admin()->check() && trim($pswd)!=='')
			{

				$input = array(

						'password' => Hash::make($pswd)

					);

				Tbl_user::where('user_id','=',$id)->update($input);

				return Redirect::back()->with('message', 'Password Updated Successfully');

			}

			return Response::json(array(array('status' => '1','message' => 'User deleted successfully','alert'=>'alert-success')));

	}

	public function adminDelete($id){

			//user data
			$user = Tbl_user::find($id);

            $user->update(array(
                
                'user_status' => 'deleted'

                ));
            /*
                //delete products
                $products = Tbl_product::where('product_user_id',$id)->get();

                $destinationPath = public_path().'/img/user/products/';

                $destinationPathThumb = public_path().'/img/user/products/thumb/';

                foreach ($products as $p) {
                    
                    File::delete($destinationPath.''.$p->product_img);

                    File::delete($destinationPathThumb.''.$p->product_img);

                    $p->delete();

                }
                //delete customers
                $customers = Tbl_customer::where('customer_user_id',$id)->get();

                foreach ($customers as $c) {

                    $c->delete();

                }

    			//delete user
    			$user->delete();
            */

			return Response::json(array(array('status' => '1','message' => 'User status changed to deleted successfully','alert'=>'alert-success')));

	}

	public function adminStore(){

		$address = Input::get('country')."|".Input::get('city')."|".Input::get('address');

			$input = array('user_fullname' => Input::get('name'),
							'user_img_profile' => 'avatar5.png',
							'user_typeid' => Input::get('type'),
							'user_business_name' => Input::get('name'),
												'user_business_cat'   => Input::get('cat'),
												'password'  => Hash::make(Input::get('pswd')),
												'user_address' => $address,
												'user_status' => 'non-aktif',
												'user_zip' => Input::get('zip'),
												'user_phone_number' => Input::get('contact'),
												'email' => Input::get('email'),
												'user_role' => 'member');

			$semuauser = Tbl_user::all();

			//Checking email for duplicate
			foreach ($semuauser as $cekuser) {
				if ($cekuser->email == Input::get('email')) {
					return Redirect::back()
								->with('message', 'E-mail sudah pernah di registrasi.');
				}
			}

			//Checking email for empty variables
				$validation = Validator::make($input, Tbl_user::$rules);

				if ($validation->passes()){

					//Creating user data
						Tbl_user::create($input);

						//Creting hash kode
						$kode = Hash::make(Input::get('password').'dan'.Input::get('name').'dan'.Input::get('email'));

						$newusers = Tbl_user::where('email','=',Input::get('email'))->get();

						foreach ($newusers as $newuser) {

							$idnewuser = $newuser->user_id;

						}

						//Creating validation data
						Tbl_user::where('email','=',Input::get('email'))->update(array('user_validation_code'=>$kode));

						//Kirim Email
						$sendmail = Mail::send('emails.uservalidation', array('nama'=> Input::get('nama'),'kode'=> $kode), function($message) {
					$message->to(Input::get('email'), Input::get('name'))->subject('Welcome Pinterior!');
			});

						return Redirect::back()->with('message', 'E-mail telah dikirim ke <b>'.Input::get('email').'</b> untuk veritifikasi e-mail.');
				}

				return Redirect::back()
						->withErrors($validation)
						->with('message', 'There were validation errors.');
	}

    /**
    *
    * Generate User Token is noexist
    * @param user id
    * @return data
    *
    **/
    public static function generateUserToken($id){

        $user = Tbl_user::find($id);

        if(is_object($user)){

            $token = $user->token;

            if(!is_object($token)){

                /* generate new token if noexist */
                $token = Crypt::encrypt($id).'.'.Crypt::encrypt($user->email).'.'.Crypt::encrypt(date('YmdHms'));

                $input = array(

                    'token_user_id' => $id,
                    'token_data' => $token,
                    'token_expires_on' => date('Y-m-d H:m:s', strtotime('+7 day', strtotime(date('Y-m-d H:m:s'))))

                    );
                
                $token = Tbl_token::create($input);

                return $token;

            }else{

                //extends token for a week
                $newtoken = Crypt::encrypt($id).'.'.Crypt::encrypt($user->email).'.'.Crypt::encrypt(date('YmdHms'));

                if(date('Y-m-d',strtotime($token->updated_at)) < date('Y-m-d')){

                    $date = strtotime($token->token_expires_on) < strtotime(date('Y-m-d H:m:s')) ? strtotime(date('Y-m-d H:m:s')) : strtotime($token->token_expires_on);

                    $input = array(

                        'token_data' => $newtoken,
                        'token_expires_on' => date('Y-m-d H:m:s', strtotime('+20 day', $date))

                        );

                    $token->update($input);

                }

                return $token;
            }

        }

        return false;
    
    }

	public function interpersonate($id){

		if(Auth::admin()->check()){

			if (trim(Tbl_user::find($id))!=='' ) {

					if (Auth::user()->check()) {

						Auth::user()->logout();

					}

					Auth::admin()->impersonate('user', $id, true);

					return Redirect::to('member');

			}

					return Redirect::back()
							->withErrors($validation)
							->with('message', 'User doesnt exist.');
		}
			
			return Redirect::back()
							->withErrors($validation)
							->with('message', 'User doesnt exist.');		
	}

}
