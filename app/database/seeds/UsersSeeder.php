<?php

class UsersSeeder extends Seeder {

	public function run()
	{
		// Uncomment the below to wipe the table clean before populating
		// DB::table('isevents')->truncate();
		$isevents = array(
			[
				'user_fullname'     => 'Admin',
				'user_img_profile' => 'avatar5.png',
				'user_description' => 'Lorem',
				'user_validation_code' => 'asdsad',
				'user_birthdate' => date("Y-m-d"),
				'user_phone_number' => '0856',
				'user_status' => 'aktif',
				'email'    => 'admin@gmail.com',
				'user_role'=>'member',
				'password' => Hash::make('admin123')
			]		
		);

		// Uncomment the below to run the seeder
		DB::table('tbl_users')->insert($isevents);
	}

}