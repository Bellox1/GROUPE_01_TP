<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            CourseSeeder::class,
            ClassroomSeeder::class,
            GroupSeeder::class,
            CourseSessionSeeder::class,
        ]);
    }

    /**
     * Seed minimal data for quick testing.
     */
    public function runMinimal(): void
    {
        $this->call([
            MinimalSeeder::class,
        ]);
    }
}
