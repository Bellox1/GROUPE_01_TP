<?php

namespace Tests\Unit;

use App\Models\Classroom;
use App\Models\Course;
use App\Models\Group;
use App\Models\User;
use App\Services\ConflictDetectionService;
use App\Services\SessionCreationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuotaServiceTest extends TestCase
{
    use RefreshDatabase;

    private SessionCreationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $conflictDetectionService = new ConflictDetectionService;
        $this->service = new SessionCreationService($conflictDetectionService);
    }

    public function test_cannot_create_session_if_quota_is_exceeded()
    {
        // GIVEN: A course with 2 hours quota for CM
        $course = Course::factory()->create([
            'quota_cm' => 2,
        ]);
        $classroom = Classroom::factory()->create();
        $teacher = User::factory()->create(['role' => 'teacher']);
        $group = Group::factory()->create();

        // AND: An existing 1.5 hour session
        $this->service->createSession([
            'course_id' => $course->id,
            'classroom_id' => $classroom->id,
            'teacher_id' => $teacher->id,
            'group_id' => $group->id,
            'start_time' => now()->setTime(8, 0),
            'end_time' => now()->setTime(9, 30),
            'type' => 'CM',
        ]);

        // WHEN: Trying to add another 1 hour session (Total = 2.5h > 2h quota)
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Quota reached for this type of session.');

        $this->service->createSession([
            'course_id' => $course->id,
            'classroom_id' => $classroom->id,
            'teacher_id' => $teacher->id,
            'group_id' => $group->id,
            'start_time' => now()->setTime(10, 0),
            'end_time' => now()->setTime(11, 0),
            'type' => 'CM',
        ]);
    }

    public function test_can_create_session_if_within_quota()
    {
        $course = Course::factory()->create([
            'quota_cm' => 3,
        ]);
        $classroom = Classroom::factory()->create();
        $teacher = User::factory()->create(['role' => 'teacher']);
        $group = Group::factory()->create();

        $session = $this->service->createSession([
            'course_id' => $course->id,
            'classroom_id' => $classroom->id,
            'teacher_id' => $teacher->id,
            'group_id' => $group->id,
            'start_time' => now()->setTime(8, 0),
            'end_time' => now()->setTime(10, 0),
            'type' => 'CM',
        ]);

        $this->assertNotNull($session);
        $this->assertDatabaseHas('course_sessions', ['type' => 'CM']);
    }
}
