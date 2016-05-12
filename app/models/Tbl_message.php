<?php

class Tbl_message extends Eloquent{

	protected $primaryKey = 'message_id';
	protected $guarded = array();

	
	protected $fillable = array(

			'message_body',
			'message_c_id',
			'message_user_id',
			'message_status',
			'message_type',
			'message_last_sync',
			'message_sync_status',
			'updated_at',
			'created_at'

		);
	
	public static $rules = array(

	    'message_body' =>'required',
	    'message_c_id' =>'required'

	  );

    public function conversation(){

       return $this->belongsTo('Tbl_keyword', 'channel_keyword_id', 'keyword_id');

    }

    public function user(){

       return $this->belongsTo('Tbl_user', 'message_user_id', 'user_id');

    }

}
