<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('users')->insert([
            [
                'name' => 'Alex A. Abelos',
                'email' => 'aaabelos@faculty.mcu.edu.ph',
                'password' => null,
                'job_title' => null,
                'department' => null,
                'role' => 'faculty',
                'status' => 'active',
                'provider' => null,
                'provider_id' => null,
                'avatar' => null,
                'remember_token' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Brylle Raphael U. Bigcas',
                'email' => 'brbigcas@faculty.mcu.edu.ph',
                'password' => null,
                'job_title' => null,
                'department' => null,
                'role' => 'faculty',
                'status' => 'active',
                'provider' => null,
                'provider_id' => null,
                'avatar' => null,
                'remember_token' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Elmer Bondoc',
                'email' => 'ebondoc@faculty.mcu.edu.ph',
                'password' => null,
                'job_title' => null,
                'department' => null,
                'role' => 'faculty',
                'status' => 'active',
                'provider' => null,
                'provider_id' => null,
                'avatar' => null,
                'remember_token' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Ryan Christopher F. Cruz',
                'email' => 'rccruz@faculty.mcu.edu.ph',
                'password' => null,
                'job_title' => null,
                'department' => null,
                'role' => 'faculty',
                'status' => 'active',
                'provider' => null,
                'provider_id' => null,
                'avatar' => null,
                'remember_token' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Abigail M. De Lios',
                'email' => 'amdelios@faculty.mcu.edu.ph',
                'password' => null,
                'job_title' => null,
                'department' => null,
                'role' => 'faculty',
                'status' => 'active',
                'provider' => null,
                'provider_id' => null,
                'avatar' => null,
                'remember_token' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
