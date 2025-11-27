<?php

namespace Database\Seeders;

use App\Models\Classroom;
use Illuminate\Database\Seeder;

class ClassroomSeeder extends Seeder
{
    public function run(): void
    {
        $classrooms = [
            ['name' => 'Room 101', 'capacity' => 30, 'type' => 'ClassRoom'],
            ['name' => 'Room 102', 'capacity' => 30, 'type' => 'ClassRoom'],
            ['name' => 'Room 201', 'capacity' => 25, 'type' => 'ClassRoom'],
            ['name' => 'Room 202', 'capacity' => 25, 'type' => 'ClassRoom'],
            ['name' => 'Lab A', 'capacity' => 20, 'type' => 'Lab'],
            ['name' => 'Lab B', 'capacity' => 20, 'type' => 'Lab'],
            ['name' => 'Amphitheater A', 'capacity' => 150, 'type' => 'Amphitheater'],
            ['name' => 'Amphitheater B', 'capacity' => 100, 'type' => 'Amphitheater'],
        ];

        foreach ($classrooms as $classroom) {
            Classroom::create($classroom);
        }

        Classroom::factory(4)->create();
    }
}
