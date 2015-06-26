<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFilesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		//
		Schema::create('documents',function($table){
			$table->increments('documents_id');
			$table->string('path',50);
			$table->string('fileName',255);
			$table->string('fileType',50);
			$table->string('systemName',20);
			$table->string('airlineName',255);
			$table->string('tickeNumebr',50);
			$table->string('dateString',50);
			$table->string('orderOfDay',50);
			$table->string('fileContent',65535);
			$table->date('dateOfFile',50);
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
		//
		Schema::drop('documents');
	}

}
