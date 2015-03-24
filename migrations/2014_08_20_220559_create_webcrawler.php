<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWebcrawler extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		/* only in mysql migrate to postgres?
		Schema::create('webcrawlers', function($table)
		{
	    $table->engine = 'MyISAM';
	    $table->increments('id');
	    $table->string('page_title');
	    $table->string('page_url');
	    $table->text('page_content');
	    $table->timestamps();
		});

		DB::statement('ALTER TABLE webcrawlers ADD FULLTEXT fulltext_page_content(page_content)');
		*/
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		/*
		Schema::table('webcrawlers', function($table) {
			$table->dropIndex('fulltext_page_content');
		});
		Schema::drop('webcrawlers');
		*/

	}

}
