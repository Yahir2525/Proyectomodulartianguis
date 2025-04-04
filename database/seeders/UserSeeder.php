<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // User::create([
        //     'name' => 'Admin',
        //     'email' => 'admin@example.com',
        //     'password' => bcrypt('soyadmin'),
        //     // 'role_id' => Role::where('name', 'administrador')->first()->id,
        //     'email_verified_at' => now(),
        //     'remember_token' => Str::random(10),

        // ]);
        User::factory()->count(10)->create();
    }
}
