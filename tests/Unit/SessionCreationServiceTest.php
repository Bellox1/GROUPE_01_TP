<?php

namespace Tests\Unit;

use App\Models\Classroom;
use App\Models\Course;
use App\Models\CourseSession;
use App\Models\Group;
use App\Models\User;
use App\Services\ConflictDetectionService;
use App\Services\SessionCreationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SessionCreationServiceTest extends TestCase
{
    use RefreshDatabase;

    private SessionCreationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $conflictDetectionService = new ConflictDetectionService;
        $this->service = new SessionCreationService($conflictDetectionService);
    }

    public function test_can_create_basic_session()
    {
        $course = Course::factory()->create();
        $classroom = Classroom::factory()->create(['capacity' => 30]);
        $teacher = User::factory()->create(['role' => 'teacher']);
        $group = Group::factory()->create();

        $sessionData = [
            'course_id' => $course->id,
            'classroom_id' => $classroom->id,
            'teacher_id' => $teacher->id,
            'group_id' => $group->id,
            'start_time' => now()->addDay()->setTime(9, 0),
            'end_time' => now()->addDay()->setTime(10, 30),
            'type' => 'lecture',
        ];

        $session = $this->service->createSession($sessionData);

        $this->assertInstanceOf(CourseSession::class, $session);
        $this->assertDatabaseHas('course_sessions', $sessionData);
    }

    public function test_end_time_must_be_after_start_time()
    {
        $course = Course::factory()->create();
        $classroom = Classroom::factory()->create();
        $teacher = User::factory()->create(['role' => 'teacher']);
        $group = Group::factory()->create();

        $sessionData = [
            'course_id' => $course->id,
            'classroom_id' => $classroom->id,
            'teacher_id' => $teacher->id,
            'group_id' => $group->id,
            'start_time' => now()->addDay()->setTime(10, 30),
            'end_time' => now()->addDay()->setTime(9, 0), // End before start
            'type' => 'lecture',
        ];

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('End time must be after start time.');

        $this->service->createSession($sessionData);
    }

    public function test_teacher_must_be_teacher_role()
    {
        $course = Course::factory()->create();
        $classroom = Classroom::factory()->create();
        $student = User::factory()->create(['role' => 'student']); // Not a teacher
        $group = Group::factory()->create();

        $sessionData = [
            'course_id' => $course->id,
            'classroom_id' => $classroom->id,
            'teacher_id' => $student->id,
            'group_id' => $group->id,
            'start_time' => now()->addDay()->setTime(9, 0),
            'end_time' => now()->addDay()->setTime(10, 30),
            'type' => 'lecture',
        ];

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Assigned user must be a teacher.');

        $this->service->createSession($sessionData);
    }

    public function test_validates_required_fields()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing required fields: course_id, classroom_id, teacher_id, group_id, start_time, end_time, type');

        $this->service->createSession([]);
    }

    public function test_can_create_session_with_custom_type()
    {
        $course = Course::factory()->create();
        $classroom = Classroom::factory()->create(['capacity' => 30]);
        $teacher = User::factory()->create(['role' => 'teacher']);
        $group = Group::factory()->create();

        $sessionData = [
            'course_id' => $course->id,
            'classroom_id' => $classroom->id,
            'teacher_id' => $teacher->id,
            'group_id' => $group->id,
            'start_time' => now()->addDay()->setTime(14, 0),
            'end_time' => now()->addDay()->setTime(15, 30),
            'type' => 'lab',
        ];

        $session = $this->service->createSession($sessionData);

        $this->assertEquals('lab', $session->type);
        $this->assertDatabaseHas('course_sessions', $sessionData);
    }

    public function test_session_returns_relationships()
    {
        $course = Course::factory()->create();
        $classroom = Classroom::factory()->create(['capacity' => 30]);
        $teacher = User::factory()->create(['role' => 'teacher']);
        $group = Group::factory()->create();

        $sessionData = [
            'course_id' => $course->id,
            'classroom_id' => $classroom->id,
            'teacher_id' => $teacher->id,
            'group_id' => $group->id,
            'start_time' => now()->addDay()->setTime(9, 0),
            'end_time' => now()->addDay()->setTime(10, 30),
            'type' => 'lecture',
        ];

        $session = $this->service->createSession($sessionData);

        $this->assertEquals($course->id, $session->course->id);
        $this->assertEquals($classroom->id, $session->classroom->id);
        $this->assertEquals($teacher->id, $session->teacher->id);
        $this->assertEquals($group->id, $session->group->id);
    }

    public function test_detects_room_conflict_during_creation()
    {
        $course = Course::factory()->create();
        $classroom = Classroom::factory()->create(['capacity' => 30]);
        $teacher = User::factory()->create(['role' => 'teacher']);
        $group = Group::factory()->create();

        // Create existing session
        CourseSession::factory()->create([
            'classroom_id' => $classroom->id,
            'start_time' => now()->addDay()->setTime(9, 0),
            'end_time' => now()->addDay()->setTime(10, 30),
        ]);

        $sessionData = [
            'course_id' => $course->id,
            'classroom_id' => $classroom->id, // Same classroom
            'teacher_id' => $teacher->id,
            'group_id' => $group->id,
            'start_time' => now()->addDay()->setTime(10, 0), // Overlapping time
            'end_time' => now()->addDay()->setTime(11, 30),
            'type' => 'lecture',
        ];

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Scheduling conflicts detected');

        $this->service->createSession($sessionData);
    }
}
