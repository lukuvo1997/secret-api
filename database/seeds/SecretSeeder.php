<?php

use Illuminate\Database\Seeder;
use Carbon\Carbon;
use Faker\Generator as Faker;

class SecretSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(Faker $faker)
    {
        for ($i=0; $i < 50; $i++) { 
            $minutes = rand(0,100);
            DB::table('secret')->insert([
                'hash' => base64_encode(Hash::make('secret')),
                'name' => $faker->sentence,
                'remaining_views' => rand(1,100),
                'minutes' => $minutes,
                'expires_at' => Carbon::now()->addMinutes($minutes),
                'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                'updated_at' => Carbon::now()->format('Y-m-d H:i:s')
            ]);
        }
    }
}
