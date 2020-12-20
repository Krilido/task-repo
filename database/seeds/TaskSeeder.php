<?php

use Illuminate\Database\Seeder;
use Faker\Factory as Faker;
use App\Model\Task;
use Carbon\Carbon;

class TaskSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker::create('id_ID');
 
    	for($i = 1; $i <= 150; $i++){
 
    	      // insert data ke table pegawai menggunakan Faker
    		DB::table('tasks')->insert([
                'name' => $faker->name,
                'section_id' => $faker->numberBetween(1,50),
                'status' => Task::ACTIVE,
                'progress' => $faker->numberBetween(0,1),
                'description' => "some Description",
                'created_at' => Carbon::now()
    		]);
 
    	}
    }
}
