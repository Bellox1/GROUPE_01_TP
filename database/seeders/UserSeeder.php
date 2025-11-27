<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::factory()->admin()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
        ]);

        User::factory()->teacher()->create([
            'name' => 'John Teacher',
            'email' => 'teacher@example.com',
        ]);

        User::factory()->teacher()->create([
            'name' => 'Sarah Professor',
            'email' => 'sarah@example.com',
        ]);

        User::factory()->teacher()->create([
            'name' => 'Michael Instructor',
            'email' => 'michael@example.com',
        ]);

        User::factory()->create([
            'name' => 'Alice Student',
            'email' => 'alice@example.com',
        ]);

        User::factory()->create([
            'name' => 'Bob Student',
            'email' => 'bob@example.com',
        ]);

        User::factory(900)->create();
        User::factory(5)->teacher()->create();
    }
}
