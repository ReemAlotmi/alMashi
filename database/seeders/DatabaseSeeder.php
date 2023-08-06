<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {

        //$this->call(UsersTableSeeder::class);
        //$this->call(OtherSeeder::class);
        //instead of putting the below code we can create a class seperatly for each table
        //and just call the calss here

         // Generate and seed users
        //  for ($i = 0; $i < 20; $i++) {
        //     $mobileNo = '05'.mt_rand(10000000, 99999999);
        //     $rating = number_format(mt_rand(0, 500) / 100, 1);
        //     $profileImg = Str::random(30);
        //     $currentLocation = mt_rand(-90, 90).','.mt_rand(-180, 180);

        //     $user= DB::table('users')->insert([
        //         'mobile_no' => $mobileNo,
        //         'name' => Str::random(10),
        //         'rating' => $rating,
        //         'profile_img' => $profileImg,
        //         'is_driver' => '0',
        //         'current_location' => $currentLocation,
        //     ]);
        //     $user->createToken("API TOKEN")->plainTextToken;
        // }

       
    }
}
