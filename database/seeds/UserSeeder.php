<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Date;
use App\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->insert([
            [
                'name' => 'Yotsuba Nakano',
                'email' => 'nakano.yotsuba@gmail.com',
                'password' => Hash::make('password'),
                'photo' =>'discord.png',
                'created_at' => Date::now(),
                'updated_at' => Date::now()

            ],
            [
                'name' => 'Chika Fujiwara',
                'email' => 'c.fujiwara@gmail.com',
                'password' => Hash::make('password'),
                'photo' => 'instagram.png',
                'created_at' => Date::now(),
                'updated_at' => Date::now()
            ],
            [
                'name' => 'Iroha Isshiki',
                'email' => 'isshiki.iroha@gmail.com',
                'password' => Hash::make('password'),
                'photo' => 'discord.png',
                'created_at' => Date::now(),
                'updated_at' => Date::now()
            ],
            [
                'name' => 'Katou Megumi',
                'email' => 'katoumegumi@gmail.com',
                'password' => Hash::make('password'),
                'photo' => 'github.png',
                'created_at' => Date::now(),
                'updated_at' => Date::now()
            ],
            [
                'name' => 'Ichika Nakano',
                'email' => 'nakano.ichika@gmail.com',
                'password' => Hash::make('password'),
                'photo' => 'twitter.png',
                'created_at' => Date::now(),
                'updated_at' => Date::now()
            ]
        ]);
    }
}
