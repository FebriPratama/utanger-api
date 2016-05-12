<?php

class Tbl_token extends Eloquent{

	protected $primaryKey = 'token_id';
	protected $guarded = array();

		    

	protected $fillable = array('token_user_id','token_data','token_expires_on','token_client','updated_at','created_at');

	public static $rules = array(

	    'token_user_id' => 'required',
	    'token_data' => 'required',
	    'token_expires_on' => 'required'

	  );

    public function user()
    {
        return $this->hasOne('Tbl_user', 'user_id', 'token_user_id');
    }

}
