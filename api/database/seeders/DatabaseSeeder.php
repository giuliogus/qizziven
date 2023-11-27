<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('admin'),
            'admin' => true,
        ]);

        for ($i = 1; $i <= 4; $i++) {
            User::create([
                'name' => "Player {$i}",
                'email' => "player{$i}@example.com",
                'password' => Hash::make("player{$i}"),
            ]);
        }
    }
}
