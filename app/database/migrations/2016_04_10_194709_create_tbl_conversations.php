<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblConversations extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('tbl_message_conversations',function(Blueprint $table){

			$table->increments('mc_id');
			$table->integer('mc_user_one');
			$table->integer('mc_user_two');
			$table->datetime('mc_last_sync');
			$table->string('mc_sync_status');
			$table->timestamps();

		});
    }

    public function down()
    {
        Schema::drop('tbl_message_conversations');
    }

}
