<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblFriendTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('tbl_friends',function(Blueprint $table){

			$table->increments('friend_id');
			$table->integer('friend_one');
			$table->integer('friend_two');
			$table->timestamps();

		});
    }

    public function down()
    {
        Schema::drop('tbl_friends');
    }

}