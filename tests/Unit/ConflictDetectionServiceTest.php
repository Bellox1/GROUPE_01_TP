<?php

namespace Tests\Unit;

use App\Models\Classroom;
use App\Models\Course;
use App\Models\CourseSession;
use App\Models\Group;
use App\Models\User;
use App\Services\ConflictDetectionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConflictDetectionServiceTest extends TestCase
{
    use RefreshDatabase;

    private ConflictDetectionService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ConflictDetectionService;
    }

    public function test_detects_no_conflicts_for_valid_session()
    {
        $sessionData = $this->createValidSessionData();

        $conflicts = $this->service->detectConflicts($sessionData);

        $this->assertEmpty($conflicts);
    }

    public function test_detects_room_conflict()
    {
        $classroom = Classroom::factory()->create();
        $existingSession = CourseSession::factory()->create([
            'classroom_id' => $classroom->id,
            'start_time' => now()->setTime(10, 0),
            'end_time' => now()->setTime(11, 30),
        ]);

        $conflictingSessionData = [
            'classroom_id' => $classroom->id,
            'teacher_id' => User::factory()->create(['role' => 'teacher'])->id,
            'group_id' => Group::factory()->create()->id,
            'course_id' => Course::factory()->create()->id,
            'start_time' => now()->setTime(10, 30), // Overlaps with existing session
            'end_time' => now()->setTime(12, 0),
        ];

        $conflicts = $this->service->detectConflicts($conflictingSessionData);

        $this->assertArrayHasKey('room', $conflicts);
        $this->assertEquals('room_conflict', $conflicts['room']['type']);
        $this->assertStringContainsString('already booked', $conflicts['room']['message']);
    }

    public function test_detects_teacher_conflict()
    {
        $teacher = User::factory()->create(['role' => 'teacher']);
        $existingSession = CourseSession::factory()->create([
            'teacher_id' => $teacher->id,
            'start_time' => now()->setTime(14, 0),
            'end_time' => now()->setTime(15, 30),
        ]);

        $conflictingSessionData = [
            'classroom_id' => Classroom::factory()->create()->id,
            'teacher_id' => $teacher->id, // Same teacher
            'group_id' => Group::factory()->create()->id,
            'course_id' => Course::factory()->create()->id,
            'start_time' => now()->setTime(15, 0), // Overlaps with existing session
            'end_time' => now()->setTime(16, 30),
        ];

        $conflicts = $this->service->detectConflicts($conflictingSessionData);

        $this->assertArrayHasKey('teacher', $conflicts);
        $this->assertEquals('teacher_conflict', $conflicts['teacher']['type']);
        $this->assertStringContainsString('already teaching', $conflicts['teacher']['message']);
    }

    public function test_detects_group_conflict()
    {
        $group = Group::factory()->create();
        $existingSession = CourseSession::factory()->create([
            'group_id' => $group->id,
            'start_time' => now()->setTime(9, 0),
            'end_time' => now()->setTime(10, 30),
        ]);

        $conflictingSessionData = [
            'classroom_id' => Classroom::factory()->create()->id,
            'teacher_id' => User::factory()->create(['role' => 'teacher'])->id,
            'group_id' => $group->id, // Same group
            'course_id' => Course::factory()->create()->id,
            'start_time' => now()->setTime(10, 0), // Overlaps with existing session
            'end_time' => now()->setTime(11, 30),
        ];

        $conflicts = $this->service->detectConflicts($conflictingSessionData);

        $this->assertArrayHasKey('group', $conflicts);
        $this->assertEquals('group_conflict', $conflicts['group']['type']);
        $this->assertStringContainsString('already has a class', $conflicts['group']['message']);
    }

    public function test_detects_capacity_constraint()
    {
        $classroom = Classroom::factory()->create(['capacity' => 20]);
        $group = Group::factory()->create();

        // Create 25 students for the group
        $students = User::factory()->count(25)->create(['role' => 'student']);
        $group->students()->attach($students->pluck('id'));

        $sessionData = [
            'classroom_id' => $classroom->id,
            'teacher_id' => User::factory()->create(['role' => 'teacher'])->id,
            'group_id' => $group->id,
            'course_id' => Course::factory()->create()->id,
            'start_time' => now()->addDay()->setTime(9, 0),
            'end_time' => now()->addDay()->setTime(10, 30),
        ];

        $conflicts = $this->service->detectConflicts($sessionData);

        $this->assertArrayHasKey('capacity', $conflicts);
        $this->assertEquals('capacity_constraint', $conflicts['capacity']['type']);
        $this->assertStringContainsString('insufficient for group size', $conflicts['capacity']['message']);
        $this->assertEquals(20, $conflicts['capacity']['classroom_capacity']);
        $this->assertEquals(25, $conflicts['capacity']['group_size']);
        $this->assertEquals(5, $conflicts['capacity']['overflow']);
    }

    public function test_detects_multiple_conflicts()
    {
        $classroom = Classroom::factory()->create(['capacity' => 10]);
        $teacher = User::factory()->create(['role' => 'teacher']);
        $group = Group::factory()->create();

        // Create 15 students for the group (exceeds capacity)
        $students = User::factory()->count(15)->create(['role' => 'student']);
        $group->students()->attach($students->pluck('id'));

        // Create existing sessions that conflict
        CourseSession::factory()->create([
            'classroom_id' => $classroom->id,
            'start_time' => now()->setTime(10, 0),
            'end_time' => now()->setTime(11, 30),
        ]);

        CourseSession::factory()->create([
            'teacher_id' => $teacher->id,
            'start_time' => now()->setTime(10, 30),
            'end_time' => now()->setTime(12, 0),
        ]);

        $sessionData = [
            'classroom_id' => $classroom->id,
            'teacher_id' => $teacher->id,
            'group_id' => $group->id,
            'course_id' => Course::factory()->create()->id,
            'start_time' => now()->setTime(10, 30),
            'end_time' => now()->setTime(12, 0),
        ];

        $conflicts = $this->service->detectConflicts($sessionData);

        $this->assertArrayHasKey('room', $conflicts);
        $this->assertArrayHasKey('teacher', $conflicts);
        $this->assertArrayHasKey('capacity', $conflicts);
    }

    public function test_excludes_session_id_from_conflict_check()
    {
        $existingSession = CourseSession::factory()->create([
            'start_time' => now()->setTime(10, 0),
            'end_time' => now()->setTime(11, 30),
        ]);

        // Update the same session with same time - should not detect conflict
        $sessionData = [
            'id' => $existingSession->id,
            'classroom_id' => $existingSession->classroom_id,
            'teacher_id' => $existingSession->teacher_id,
            'group_id' => $existingSession->group_id,
            'course_id' => $existingSession->course_id,
            'start_time' => $existingSession->start_time,
            'end_time' => $existingSession->end_time,
        ];

        $conflicts = $this->service->detectConflicts($sessionData);

        $this->assertEmpty($conflicts);
    }

    public function test_detects_edge_case_time_conflicts()
    {
        $classroom = Classroom::factory()->create();

        // Create session from 10:00 to 11:00
        CourseSession::factory()->create([
            'classroom_id' => $classroom->id,
            'start_time' => now()->setTime(10, 0),
            'end_time' => now()->setTime(11, 0),
        ]);

        // Test session that starts exactly when existing session ends
        $sessionData1 = [
            'classroom_id' => $classroom->id,
            'teacher_id' => User::factory()->create(['role' => 'teacher'])->id,
            'group_id' => Group::factory()->create()->id,
            'course_id' => Course::factory()->create()->id,
            'start_time' => now()->setTime(11, 0), // Starts when existing ends
            'end_time' => now()->setTime(12, 30),
        ];

        $conflicts1 = $this->service->detectConflicts($sessionData1);
        $this->assertEmpty($conflicts1, 'Should not detect conflict when session starts exactly when existing ends');

        // Test session that ends exactly when existing session starts
        $sessionData2 = [
            'classroom_id' => $classroom->id,
            'teacher_id' => User::factory()->create(['role' => 'teacher'])->id,
            'group_id' => Group::factory()->create()->id,
            'course_id' => Course::factory()->create()->id,
            'start_time' => now()->setTime(8, 0),
            'end_time' => now()->setTime(10, 0), // Ends when existing starts
        ];

        $conflicts2 = $this->service->detectConflicts($sessionData2);
        $this->assertEmpty($conflicts2, 'Should not detect conflict when session ends exactly when existing starts');
    }

    public function test_get_available_time_slots()
    {
        $classroom = Classroom::factory()->create();
        $date = now()->format('Y-m-d');

        // Create some sessions
        CourseSession::factory()->create([
            'classroom_id' => $classroom->id,
            'start_time' => $date.' 10:00:00',
            'end_time' => $date.' 11:30:00',
        ]);

        CourseSession::factory()->create([
            'classroom_id' => $classroom->id,
            'start_time' => $date.' 14:00:00',
            'end_time' => $date.' 15:30:00',
        ]);

        $availableSlots = $this->service->getAvailableTimeSlots($classroom->id, $date, 90);

        $this->assertNotEmpty($availableSlots);
        // Should have slots before 10:00, between 11:30 and 14:00, and after 15:30
        $this->assertGreaterThanOrEqual(2, count($availableSlots));
    }

    private function createValidSessionData(): array
    {
        return [
            'classroom_id' => Classroom::factory()->create()->id,
            'teacher_id' => User::factory()->create(['role' => 'teacher'])->id,
            'group_id' => Group::factory()->create()->id,
            'course_id' => Course::factory()->create()->id,
            'start_time' => now()->addDay()->setTime(9, 0),
            'end_time' => now()->addDay()->setTime(10, 30),
        ];
    }
}
