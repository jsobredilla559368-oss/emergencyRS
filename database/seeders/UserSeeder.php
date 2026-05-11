<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Admin User',
            'email' => '0gNl8@example.com',
            'password' => Hash::make('password'), 
            'role' => 'admin',
            'phone' => '09000000001',
        ]);


        User::create([
            'name' => 'John Doe',
            'email' => 'Responder@example.com',
            'password' => Hash::make('password'), 
            'role' => 'responder',
            'phone' => '09000000002',
        ]);

        User::create([
            'name' => 'Jane Smith',
            'email' => 'Reporter@example.com',
            'password' => Hash::make('password'),         
            'role' => 'reporter',
            'phone' => '09000000003',
            'address' => 'Davao City, Philippines',
        ]);
    }
}
