<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePagesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		$databaseConnectionType = \Config::get('database.default');

		if ($databaseConnectionType == "pgsql") {
			Schema::create('pages', function(Blueprint $table)
			{
				$table->increments('id');
				$table->timestamps();
				$table->string('title')->nullable();
				$table->string('navigation_section')->nullable();
				$table->integer('display_order')->nullable();
			});

			DB::statement('ALTER TABLE pages ADD COLUMN page_data JSON;');
		}

		
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		$databaseConnectionType = \Config::get('database.default');

		if ($databaseConnectionType == "pgsql") {
			Schema::drop('pages');
		}
	}

}
