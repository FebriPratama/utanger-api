<?php

class Tbl_notification extends Eloquent
{

	protected $primaryKey = 'notif_id';
	protected $guarded = array();

	protected $fillable = array('notif_user_id','notif_fk_id','notif_fk_type','notif_type_id','notif_status','notif_last_sync','notif_sync_status');

	public static $rules = array(

	    'notif_status' => 'required',
	    'notif_user_id' => 'required',
	    'notif_fk_id' => 'required'

	  );

    public function user()
    {
        return $this->belongsTo('Tbl_user');
    }

    public function type()
    {
        return $this->belongsTo('Tbl_notification_type', 'notif_type_id', 'notif_type_id');
    }
}
