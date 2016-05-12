<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblNotificationTypesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('tbl_notification_types',function(Blueprint $table){

			$table->increments('notif_type_id');
			$table->string('notif_type_body');
			$table->string('notif_type_name');
			$table->datetime('notif_type_last_sync');
			$table->string('notif_type_sync_status');
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
		
		Schema::drop('tbl_notification_types');

	}
}
