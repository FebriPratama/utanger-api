<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblNotificationsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('tbl_notifications',function(Blueprint $table){

			$table->increments('notif_id');
			$table->integer('notif_user_id');
			$table->integer('notif_fk_id');
			$table->string('notif_fk_type');
			$table->integer('notif_type_id');
			$table->enum('notif_status',array('read','unread'));
			$table->datetime('notif_last_sync');
			$table->string('notif_sync_status');
			$table->timestamps();

		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		
		Schema::drop('tbl_notifications');

	}

}
