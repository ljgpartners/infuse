<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class InfuseDatabaseSeeder extends Seeder {

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        $this->call('InfuseTableSeeder');
    }

}




class InfuseTableSeeder extends Seeder {

    public function run()
    {
        /*DB::table('regions')->delete();
        Region::create([
            'destination_site' => 'all',
            'display_state' => 'active',
            'name' => "Inland Empire",
            'state' => "California",
        ]); */
    }

}

