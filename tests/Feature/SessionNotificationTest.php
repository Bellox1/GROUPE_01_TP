<?php

namespace Tests\Feature;

use App\Mail\SessionScheduled;
use App\Models\Classroom;
use App\Models\Course;
use App\Models\Group;
use App\Models\User;
use App\Services\ConflictDetectionService;
use App\Services\SessionCreationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class SessionNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_notifies_students_when_session_is_created()
    {
        Mail::fake();

        $course = Course::factory()->create(['name' => 'Laravel Advanced', 'quota_cm' => 10]);
        $group = Group::factory()->create();
        $student1 = User::factory()->create(['role' => 'student', 'email' => 'student1@example.com']);
        $student2 = User::factory()->create(['role' => 'student', 'email' => 'student2@example.com']);
        $otherStudent = User::factory()->create(['role' => 'student', 'email' => 'other@example.com']);

        $group->students()->attach([$student1->id, $student2->id]);

        $service = new SessionCreationService(new ConflictDetectionService());
        
        $service->createSession([
            'course_id' => $course->id,
            'classroom_id' => Classroom::factory()->create(['name' => 'Room 101'])->id,
            'teacher_id' => User::factory()->create(['role' => 'teacher', 'name' => 'John Doe'])->id,
            'group_id' => $group->id,
            'start_time' => now()->addDay()->setTime(9, 0),
            'end_time' => now()->addDay()->setTime(11, 0),
            'type' => 'CM',
        ]);

        Mail::assertQueued(SessionScheduled::class, function ($mail) use ($student1) {
            return $mail->hasTo($student1->email);
        });

        Mail::assertQueued(SessionScheduled::class, function ($mail) use ($student2) {
            return $mail->hasTo($student2->email);
        });

        Mail::assertNotQueued(SessionScheduled::class, function ($mail) use ($otherStudent) {
            return $mail->hasTo($otherStudent->email);
        });
    }
}
