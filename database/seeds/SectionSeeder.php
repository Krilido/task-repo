<?php

use Illuminate\Database\Seeder;
use Faker\Factory as Faker;
use App\Model\Section;

class SectionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker::create('id_ID');
 
    	for($i = 1; $i <= 50; $i++){
 
    	      // insert data ke table pegawai menggunakan Faker
    		DB::table('sections')->insert([
    			'name' => $faker->name,
    			'status' => Section::ACTIVE,
    			'description' => "some Description"
    		]);
 
    	}
    }
}
