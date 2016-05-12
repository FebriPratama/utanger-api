<?php

class Tbl_message_conversation extends Eloquent{

	protected $primaryKey = 'mc_id';
	protected $guarded = array();

	protected $fillable = array(
		'mc_user_one','mc_user_two','mc_last_sync','mc_sync_status','updated_at','created_at'
		);
	
	public static $rules = array(

	    'mc_user_one' =>'required',
	    'mc_user_two' =>'required'

	  );

    public function from(){

       return $this->belongsTo('Tbl_user', 'mc_user_one', 'user_id');

    }

    public function to(){

       return $this->belongsTo('Tbl_user', 'mc_user_two', 'user_id');

    }

    public function messages(){

    	return $this->hasMany('Tbl_message', 'message_c_id', 'mc_id');

    }
}
