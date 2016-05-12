<?php

use Illuminate\Auth\UserTrait;
use Illuminate\Auth\UserInterface;
use Illuminate\Auth\Reminders\RemindableTrait;
use Illuminate\Auth\Reminders\RemindableInterface;

class Tbl_user extends Eloquent implements UserInterface, RemindableInterface {

	use UserTrait, RemindableTrait;

	protected $primaryKey = 'user_id';
	
	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'tbl_users';

	/**
	 * The attributes excluded from the model's JSON form.
	 *
	 * @var array
	 */
	protected $guarded = array();
	
	protected $fillable = array(

		'user_fullname',
		'password',
		'email',
		'user_role',
		'user_status',
		'user_img_profile',
		'user_phone_number',
		'user_birthdate',
		'user_status',
		'user_address',
		'user_description',
		'user_validation_code',
		'remember_token',
		'updated_at',
		'created_at',
		'updated_at'
		
		);
	/**
	*
	* override method create
 	*
	**/

	public static function create(array $data) {

        /**
        *
        * Create folder for user
        * 
        **/

        $parent = parent::create($data);

		$permalink = $parent->user_id;
		
		$path = public_path().'/img/user/' . $permalink;

		if(!File::exists($path))
			File::makeDirectory($path);

		// create folders
			if(!File::exists($path.'/profile')){
				File::makeDirectory($path.'/profile');
				File::makeDirectory($path.'/profile/thumb');
			}

        return $parent;
	    
	}

	/**
	*
	* override method update
 	*
	**/

	public function update(array $data = array()){

		$path = public_path('img/user/' . $this->user_id);

		if(!File::exists($path)){
			File::makeDirectory($path);
		}
		// create folders
		if(!File::exists($path.'/profile')){
			File::makeDirectory($path.'/profile');
			File::makeDirectory($path.'/profile/thumb');
		}

		$parent = parent::update($data);

		$newpath = public_path('img/user/' . $this->user_id);

		// update folders
		File::move($path, $newpath);

		return $parent;
	}

	public function delete(){

		$path = public_path().'/img/user/' . $this->user_id;

		if(!File::exists($path)) {

			// delete folders
			File::deleteDirectory($path.'/profile');
			File::deleteDirectory($path.'/profile/thumb');

		}

		return $parent = parent::delete();
	}

	/**
	*
	* override method delete
 	*
	**/

	public static $rules = array(
	    'user_fullname' => 'required',
	    'user_address' => 'required',
	    'user_zip' => 'required|numeric',
	    'user_phone_number' => 'required|numeric',
	    'email' => 'required|unique:tbl_users,email',
	    'user_business_name' => 'required',
	    'user_role' => 'required',
	    'password' => 'required|confirmed|min:8'
	  );

	public static $Brandrules = array(

			'user_fullname' => 'required',
			'user_parent_id'=> 'required | numeric',
			'user_role'=> 'required', 
			'user_permalink' => 'required|unique:tbl_users,user_permalink',
			'user_currency_id'   => 'required | numeric',
			'user_color_id'   => 'required | numeric'

		);

	public static $BrandrulesUpdate = array(

			'user_fullname' => 'required',
			'user_currency_id'   => 'required | numeric',
			'user_color_id'   => 'required | numeric'

		);

	public static $rulesbiasa = array(

	    'user_fullname' => 'required|min:3',
	    'password' => 'required|confirmed|min:6',
	    'password_confirmation' => 'required|min:6',
	    'user_phone_number' => 'required|numeric',
	    'email' => 'required|unique:tbl_users,email'

	  );

	public static $rulesuserexist = array(

	    'user_fullname' => 'required|min:3',
	    'password' => 'required|confirmed|min:6',
	    'password_confirmation' => 'required|min:6',
	    'user_phone_number' => 'required|numeric',
	    'email' => 'required'

	  );

	public static $rulespermalink = array(

		'user_permalink' => 'required'

		);

	public static $rulesslide = array(
	    'user_slide' => 'required'
	  );

	public static $rulessubmember = array(
	    'user_fullname' => 'required',
	    'email' => 'required|unique:tbl_users,email',
	    'user_role' => 'required'
	  );

	public static $rulesprofile = array(
	    'user_fullname' => 'required',
	    'user_address' => 'required',
	    'user_zip' => 'required|numeric',
	    'user_phone_number' => 'required|numeric',
	    'user_birthdate' => 'required'
	  );

	public static $ruleslogin = array(
	    'email' => 'required|email',
	    'password' => 'required'
	  );

	public static $rulessocial = array(
	    'user_wa' => 'required|numeric',
	    'user_website' => 'required|url',
	    'user_line' => 'required|numeric'
	  );

	public static $rulesoauth = array(
	    'user_fullname' => 'required',
	    'user_business_cat' => 'required',
	    'user_address' => 'required',
	    'user_zip' => 'required|numeric',
	    'user_phone_number' => 'required|numeric',
	    'email' => 'required|unique:tbl_users,email',
	    'user_role' => 'required',
	  );

	public static $updateRules = array(
	    'user_fullname' => 'required',
	    'user_phone_number' => 'required|numeric'
	  );

	public function getRememberToken()
	{
    	return $this->remember_token;
	}
	/*
	|	-- other
	|
	*/

	public function path(){

		$permalink = $this->user_permalink;
		
		$path = public_path().'/img/user/' . $permalink;

		return $path;
	}

	public function createFolderUser(){

		$permalink = $this->user_permalink;
		
		$path = public_path().'/img/user/' . $permalink;

		// create folder profile adn profile

		if( File::makeDirectory($path.'/profile') && File::makeDirectory($path.'/customer') ){

			return true;
		}

		return false;
	}

	/*
	|	-- relationship
	|
	*/

    public function token()
    {
        return $this->hasOne('Tbl_token', 'token_user_id', 'user_id');
    }

	public function sub()
	{
		return $this->hasMany('Tbl_user', 'user_parent_id', 'user_id');
	}

	public function conversationfrom()
	{
		return $this->hasMany('Tbl_message_conversation', 'mc_user_one', 'user_id');
	}

	public function conversationto()
	{
		return $this->hasMany('Tbl_message_conversation', 'mc_user_two', 'user_id');
	}

	public function message()
	{
		return $this->hasMany('Tbl_message', 'message_user_id', 'user_id');
	}

	public function parent()
	{
		return $this->belongsTo('Tbl_user', 'user_parent_id', 'user_id');
	}

	public function payment()
	{
		return $this->belongsTo('Tbl_payment', 'user_id', 'payment_user_id');
	}

	public function voucher()
	{
		return $this->belongsTo('Tbl_voucher', 'user_id', 'voucher_user_id');
	}

	public function package()
	{
		return $this->belongsTo('Tbl_package', 'user_package_id', 'package_id');
	}

	public function currency()
	{
		return $this->belongsTo('Tbl_currency', 'user_currency_id', 'currency_id');
	}

	public function status()
	{
		return $this->hasMany('Tbl_sale_status', 'ss_user_id', 'user_id');
	}

	public function bm()
	{
		return $this->hasMany('Tbl_brand_member', 'bm_brand_id', 'user_id');
	}

	public function products()
	{
		return $this->hasMany('Tbl_product', 'product_user_id', 'user_id');
	}

	public function features()
	{
		return $this->hasMany('Tbl_feature_value', 'fv_user_id', 'user_id');
	}

	public function sales()
	{
		return $this->hasMany('Tbl_sale', 'sale_user_id', 'user_id');
	}

	public function channel()
	{
		return $this->hasMany('Tbl_channel', 'channel_user_id', 'user_id');
	}

	public function customers()
	{
	    return $this->hasMany('Tbl_customer', 'customer_user_id', 'user_id');
	}

	public function fb()
	{
	    return $this->hasMany('Tbl_fbconnect', 'fb_user_id', 'user_id');
	}

	public function tw()
	{
	    return $this->hasMany('Tbl_twconnect', 'tw_user_id', 'user_id');
	}

	public function ig()
	{
	    return $this->hasMany('Tbl_igconnect', 'ig_user_id', 'user_id');
	}

	public function notifications()
	{
	    return $this->hasMany('Tbl_notification', 'notif_user_id', 'user_id');
	}

	public function conv()
	{
	    return $this->hasMany('Tbl_c_conversation', 'cc_user_id', 'user_id');
	}

	/* delete */
	public function deleteRelation(){

		/* cust */
			/* related with customer */
			foreach($this->customers as $d){

				/* delete key */
				$d->key()->delete();

			}

		$this->customers()->delete();

		/* product */
			/* related with product */
			foreach($this->products as $d){

				/* delete key */
				$d->key()->delete();

			}

		$this->products()->delete();

		/* sos */
		$this->fb()->delete();
		$this->tw()->delete();
		$this->ig()->delete();

		/* chat */
			/* related with chat */
			foreach($this->conv as $d){

				/* delete message */
				$d->message()->delete();

			}

		$this->conv()->delete();

		/* msc */
		$this->notifications()->delete();
	}

}
