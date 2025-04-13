<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        User::create([
            'id' => 1,
            'name' => 'User',
            'email' => 'user@example.com',
            'password' => Hash::make('password'),
            'type' => 0,
        ]);

        User::create([
            'id' => 2,
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'type' => 1,
        ]);

        User::create([
            'id' => 3,
            'name' => 'vendor',
            'email' => 'vendor@example.com',
            'password' => Hash::make('password'),
            'lat' => 33.52279593033495, // خط العرض (يجب أن يكون بين -90 و 90)
            'lang' => 36.27648767156756, // خط الطول (يجب أن يكون بين -180 و 180)
            'type' => 0,
        ]);
        User::create([
            'id' => 4,
            'name' => 'vendor2',
            'email' => 'vendor2@example.com',
            'password' => Hash::make('password'),
            'lat' => 33.52298793448784, // خط العرض (يجب أن يكون بين -90 و 90)
            'lang' => 36.276547852276906, // خط الطول (يجب أن يكون بين -180 و 180)
            'type' => 0,
        ]);





    }
}
