<?php

namespace Database\Seeders;

use App\Models\Classroom;
use App\Models\Course;
use App\Models\CourseSession;
use App\Models\Group;
use App\Models\User;
use Illuminate\Database\Seeder;

class MinimalSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::factory()->admin()->create([
            'name' => 'Admin',
            'email' => 'admin@test.com',
        ]);

        $teacher = User::factory()->teacher()->create([
            'name' => 'Teacher',
            'email' => 'teacher@test.com',
        ]);

        $student = User::factory()->create([
            'name' => 'Student',
            'email' => 'student@test.com',
        ]);

        $course = Course::create([
            'code' => 'TEST101',
            'name' => 'Test Course',
            'description' => 'A test course for development',
            'credits' => 3,
        ]);

        $classroom = Classroom::create([
            'name' => 'Test Room',
            'capacity' => 30,
            'type' => 'ClassRoom',
        ]);

        $group = Group::create([
            'name' => 'Test Group',
        ]);

        $teacher->courses()->attach($course);
        $group->students()->attach($student);

        CourseSession::create([
            'course_id' => $course->id,
            'classroom_id' => $classroom->id,
            'teacher_id' => $teacher->id,
            'group_id' => $group->id,
            'start_time' => now()->addDay()->setTime(10, 0),
            'end_time' => now()->addDay()->setTime(12, 0),
            'type' => 'CM',
        ]);
    }
}
