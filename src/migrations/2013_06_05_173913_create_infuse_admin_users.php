<?php

use Illuminate\Database\Migrations\Migration;

class CreateInfuseAdminUsers extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('infuse_admin_users', function($table)
		{
		    $table->increments('id');
		    $table->string('username');
		    $table->string('email');
		    $table->string('password');
		    $table->integer('logins');
		    $table->date('last_login_date');
		    $table->string('last_login_ip');
		    $table->timestamps();
		});

		DB::table('infuse_admin_users')->insert(
        array(
            'username' => 'admin',
            'password' => Hash::make('password') 
        )
    );
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('infuse_admin_users');
	}

}