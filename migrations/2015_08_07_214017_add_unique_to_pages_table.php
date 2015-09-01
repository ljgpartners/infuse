<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddUniqueToPagesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		$databaseConnectionType = \Config::get('database.default');

		if ($databaseConnectionType == "pgsql") {
			Schema::table('pages', function(Blueprint $table)
			{
				$table->string('unique')->nullable();
			});
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
			Schema::table('pages', function(Blueprint $table)
			{
				$table->dropColumn('unique');
			});
		}
	}

}
