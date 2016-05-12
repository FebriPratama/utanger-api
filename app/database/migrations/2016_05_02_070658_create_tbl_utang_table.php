<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblUtangTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('tbl_utangs',function(Blueprint $table){

			$table->increments('utang_id');
			$table->integer('utang_peminjam');
			$table->integer('utang_pemilik');
			$table->decimal('utang_total',19,4);
			$table->datetime('utang_pinjam');
			$table->datetime('utang_kembali');
			$table->text('utang_note');
			$table->timestamps();

		});
    }

    public function down()
    {
        Schema::drop('tbl_utangs');
    }
}