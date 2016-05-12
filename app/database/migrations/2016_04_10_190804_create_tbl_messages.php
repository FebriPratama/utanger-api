<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblMessages extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('tbl_messages',function(Blueprint $table){

			$table->increments('message_id');
			$table->text('message_body');
			$table->integer('message_c_id');
			$table->integer('message_user_id');
			$table->enum('message_type',array('text' , 'image'));
			$table->enum('message_status',array('read' , 'unread'));
			$table->datetime('message_last_sync');
			$table->string('message_sync_status');
			$table->timestamps();

		});
    }

    public function down()
    {
        Schema::drop('tbl_messages');
    }


}
