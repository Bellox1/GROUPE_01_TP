<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Course;
use App\Models\Classroom;
use App\Models\Group;
use App\Models\CourseSession;

class FoundationTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_users_with_roles(): void
    {
        $student = User::factory()->create();
        $this->assertEquals('student', $student->role);

        $teacher = User::factory()->teacher()->create();
        $this->assertEquals('teacher', $teacher->role);

        $admin = User::factory()->admin()->create();
        $this->assertEquals('admin', $admin->role);
    }

    public function test_can_create_course(): void
    {
        $course = Course::factory()->create();
        $this->assertDatabaseHas('courses', ['id' => $course->id]);
    }

    public function test_can_create_classroom(): void
    {
        $classroom = Classroom::factory()->create();
        $this->assertDatabaseHas('classrooms', ['id' => $classroom->id]);
    }

    public function test_can_create_group_with_students(): void
    {
        $group = Group::factory()->create();
        $students = User::factory()->count(3)->create();
        $group->students()->attach($students);

        $this->assertDatabaseHas('groups', ['id' => $group->id]);
        $this->assertCount(3, $group->students);
    }

    public function test_can_create_course_session(): void
    {
        $session = CourseSession::factory()->create();
        $this->assertDatabaseHas('course_sessions', ['id' => $session->id]);
        
        $this->assertNotNull($session->course);
        $this->assertNotNull($session->classroom);
        $this->assertNotNull($session->teacher);
        $this->assertNotNull($session->group);
    }
}
