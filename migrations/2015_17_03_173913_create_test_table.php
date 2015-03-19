<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTestTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $databaseConnectionType = \Config::get('database.default');

        if (env('APP_ENV') == "behat" && $databaseConnectionType == "pgsql") {

            Schema::create('infuse_tests', function(Blueprint $table)
            {
                $table->increments('id');
                $table->timestamps();
                $table->string('name_string')->nullable();
                $table->text('name_text')->nullable();
                $table->timestamp('name_timestamp')->nullable();
                $table->boolean('name_boolean')->nullable();
                $table->integer('name_integer')->nullable();
                $table->float('name_float')->nullable();
                $table->string('name_hstore')->nullable();
            });

            DB::statement("ALTER TABLE infuse_tests ALTER name_hstore TYPE hstore USING (name_hstore::hstore);");
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

        if (env('APP_ENV') == "behat" && $databaseConnectionType == "pgsql") {
            Schema::drop('infuse_tests');
        }
    }

}
