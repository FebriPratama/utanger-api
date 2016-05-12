<?php

class Tbl_notification_type extends Eloquent{

	protected $primaryKey = 'notif_type_id';
	protected $guarded = array();

	protected $fillable = array('notif_type_body','notif_type_name','notif_type_last_sync','notif_type_sync_status');

	public static $rules = array(
	    'notif_type_body' => 'required',
	    'notif_type_name' => 'required|unique:tbl_users,user_permalink'
	  );
	
	public static $rulesupdate = array(
	    'notif_type_body' => 'required',
	    'notif_type_name' => 'required'
	  );

}
