<?php

namespace Tests\Unit;

use App\Models\Classroom;
use App\Models\Course;
use App\Models\CourseSession;
use App\Models\Group;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CourseSessionConflictTraitTest extends TestCase
{
    use RefreshDatabase;

    private CourseSession $session;

    private Classroom $classroom;

    private User $teacher;

    private Group $group;

    private Course $course;

    protected function setUp(): void
    {
        parent::setUp();

        $this->classroom = Classroom::factory()->create(['capacity' => 30]);
        $this->teacher = User::factory()->create();
        $this->group = Group::factory()->create();
        $this->course = Course::factory()->create();

        $this->session = CourseSession::factory()->create([
            'course_id' => $this->course->id,
            'classroom_id' => $this->classroom->id,
            'teacher_id' => $this->teacher->id,
            'group_id' => $this->group->id,
            'start_time' => '2024-01-15 10:00:00',
            'end_time' => '2024-01-15 11:30:00',
        ]);
    }

    public function test_detect_conflicts_returns_empty_array_when_no_conflicts(): void
    {
        $conflicts = $this->session->detectConflicts();

        $this->assertIsArray($conflicts);
        $this->assertEmpty($conflicts);
    }

    public function test_has_conflicts_returns_false_when_no_conflicts(): void
    {
        $this->assertFalse($this->session->hasConflicts());
    }

    public function test_get_first_conflict_returns_null_when_no_conflicts(): void
    {
        $this->assertNull($this->session->getFirstConflict());
    }

    public function test_detect_conflicts_finds_room_conflict(): void
    {
        // Create a conflicting session in the same room
        $conflictingSession = CourseSession::factory()->create([
            'course_id' => $this->course->id,
            'classroom_id' => $this->classroom->id,
            'teacher_id' => User::factory()->create()->id,
            'group_id' => Group::factory()->create()->id,
            'start_time' => '2024-01-15 10:30:00',
            'end_time' => '2024-01-15 12:00:00',
        ]);

        $conflicts = $this->session->detectConflicts();

        $this->assertArrayHasKey('room', $conflicts);
        $this->assertEquals('room_conflict', $conflicts['room']['type']);
        $this->assertStringContainsString('Classroom', $conflicts['room']['message']);
    }

    public function test_has_conflicts_returns_true_when_conflicts_exist(): void
    {
        // Create a conflicting session
        CourseSession::factory()->create([
            'course_id' => $this->course->id,
            'classroom_id' => $this->classroom->id,
            'teacher_id' => User::factory()->create()->id,
            'group_id' => Group::factory()->create()->id,
            'start_time' => '2024-01-15 10:30:00',
            'end_time' => '2024-01-15 12:00:00',
        ]);

        $this->assertTrue($this->session->hasConflicts());
    }

    public function test_get_conflicting_sessions_returns_collection(): void
    {
        // Create a conflicting session
        $conflictingSession = CourseSession::factory()->create([
            'course_id' => $this->course->id,
            'classroom_id' => $this->classroom->id,
            'teacher_id' => User::factory()->create()->id,
            'group_id' => Group::factory()->create()->id,
            'start_time' => '2024-01-15 10:30:00',
            'end_time' => '2024-01-15 12:00:00',
        ]);

        $conflictingSessions = $this->session->getConflictingSessions();

        $this->assertCount(1, $conflictingSessions);
        $this->assertEquals($conflictingSession->id, $conflictingSessions->first()->id);
    }

    public function test_validate_no_conflicts_throws_exception_when_conflicts_exist(): void
    {
        // Create a conflicting session
        CourseSession::factory()->create([
            'course_id' => $this->course->id,
            'classroom_id' => $this->classroom->id,
            'teacher_id' => User::factory()->create()->id,
            'group_id' => Group::factory()->create()->id,
            'start_time' => '2024-01-15 10:30:00',
            'end_time' => '2024-01-15 12:00:00',
        ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('/Classroom.*already booked/');

        $this->session->validateNoConflicts();
    }

    public function test_validate_no_conflicts_passes_when_no_conflicts(): void
    {
        // Should not throw any exception
        $this->session->validateNoConflicts();

        $this->assertTrue(true); // Test passes if no exception is thrown
    }
}
