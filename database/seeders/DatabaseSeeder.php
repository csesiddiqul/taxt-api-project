<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->createMany([
            [
                'name' => 'Md Siddiqul Islam',
                'email' => 'labibkg@gmail.com',
                'phone' => '01905992833',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'remember_token' => str()->random(10),
            ],

            [
                'name' => 'Admin',
                'email' => 'admin@gmail.com',
                'phone' => '01905992822',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'remember_token' => str()->random(10),
            ],


            [
                'name' => 'Guest',
                'email' => 'guest@gmail.com',
                'phone' => '01796038422',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'remember_token' => str()->random(10),
            ],

            [
                'name' => 'User',
                'email' => 'user@gmail.com',
                'phone' => '01796038482',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'remember_token' => str()->random(10),
            ],


            // Add more user arrays here
        ]);
        // $this->call(SqlFilesSeeder::class);

        $this->call([
            RolePermissionSeeder::class,
            // BloodDonorInfoSeeder::class,
            // SqlFilesSeeder::class
        ]);
    }
}
