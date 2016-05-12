<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('tbl_users',function(Blueprint $table){

			$table->increments('user_id');
			$table->string('password');
			$table->string('user_img_profile');
			$table->string('user_fullname');
			$table->string('user_role');
			$table->string('email');
			$table->string('user_validation_code');
			$table->string('user_phone_number');
			$table->string('user_birthdate');
			$table->string('user_status');
			$table->string('user_address');
			$table->text('user_description');
			$table->string('remember_token');
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
		Schema::drop('tbl_users');
	}

}
