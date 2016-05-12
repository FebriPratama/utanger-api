<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblApi extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('tbl_tokens',function(Blueprint $table){

		    $table->increments('token_id');
		    $table->integer('token_user_id')->unsigned();
		    $table->text('token_data');
		    $table->timestamp('token_expires_on');
		    $table->string('token_client');

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
		Schema::drop('tbl_tokens');
	}

}
