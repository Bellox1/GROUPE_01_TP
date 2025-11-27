<?php

namespace Tests\Feature\Admin;

use App\Models\Classroom;
use App\Models\Course;
use App\Models\CourseSession;
use App\Models\Group;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CourseSessionManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create(['role' => 'admin']));
    }

    public function test_admin_can_view_sessions_list()
    {
        $sessions = CourseSession::factory()->count(3)->create();

        $response = $this->get(route('admin.sessions.index'));

        $response->assertStatus(200);
        foreach ($sessions as $session) {
            $response->assertSee($session->course->code);
            $response->assertSee($session->classroom->name);
        }
    }

    public function test_admin_can_view_create_session_form()
    {
        $response = $this->get(route('admin.sessions.create'));

        $response->assertStatus(200);
        $response->assertSee('Create Course Session');
        $response->assertSee('Course');
        $response->assertSee('Classroom');
        $response->assertSee('Teacher');
        $response->assertSee('Group');
    }

    public function test_admin_can_create_session()
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
            'start_time' => now()->addDay()->setTime(9, 0)->format('Y-m-d\TH:i'),
            'end_time' => now()->addDay()->setTime(10, 30)->format('Y-m-d\TH:i'),
            'type' => 'lecture',
        ];

        $response = $this->post(route('admin.sessions.store'), $sessionData);

        $response->assertRedirect(route('admin.sessions.index'));
        $this->assertDatabaseHas('course_sessions', [
            'course_id' => $course->id,
            'classroom_id' => $classroom->id,
            'teacher_id' => $teacher->id,
            'group_id' => $group->id,
            'type' => 'lecture',
        ]);
    }

    public function test_session_creation_validates_required_fields()
    {
        $response = $this->post(route('admin.sessions.store'), []);

        $response->assertSessionHasErrors(['course_id', 'classroom_id', 'teacher_id', 'group_id', 'start_time', 'end_time', 'type']);
    }

    public function test_session_creation_validates_end_time_after_start_time()
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
            'start_time' => now()->addDay()->setTime(10, 30)->format('Y-m-d\TH:i'),
            'end_time' => now()->addDay()->setTime(9, 0)->format('Y-m-d\TH:i'), // Before start
            'type' => 'lecture',
        ];

        $response = $this->post(route('admin.sessions.store'), $sessionData);

        $response->assertSessionHasErrors(['end_time']);
    }

    public function test_session_creation_validates_teacher_exists()
    {
        $course = Course::factory()->create();
        $classroom = Classroom::factory()->create();
        $group = Group::factory()->create();

        $sessionData = [
            'course_id' => $course->id,
            'classroom_id' => $classroom->id,
            'teacher_id' => 999, // Non-existent teacher
            'group_id' => $group->id,
            'start_time' => now()->addDay()->setTime(9, 0)->format('Y-m-d\TH:i'),
            'end_time' => now()->addDay()->setTime(10, 30)->format('Y-m-d\TH:i'),
            'type' => 'lecture',
        ];

        $response = $this->post(route('admin.sessions.store'), $sessionData);

        $response->assertSessionHasErrors(['teacher_id']);
    }

    public function test_session_creation_validates_type()
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
            'start_time' => now()->addDay()->setTime(9, 0)->format('Y-m-d\TH:i'),
            'end_time' => now()->addDay()->setTime(10, 30)->format('Y-m-d\TH:i'),
            'type' => 'invalid_type',
        ];

        $response = $this->post(route('admin.sessions.store'), $sessionData);

        $response->assertSessionHasErrors(['type']);
    }

    public function test_admin_can_view_session_details()
    {
        $session = CourseSession::factory()->create();

        $response = $this->get(route('admin.sessions.show', $session));

        $response->assertStatus(200);
        $response->assertSee($session->course->name);
        $response->assertSee($session->classroom->name);
        $response->assertSee($session->teacher->name);
        $response->assertSee($session->group->name);
    }

    public function test_admin_can_delete_session()
    {
        $session = CourseSession::factory()->create();

        $response = $this->delete(route('admin.sessions.destroy', $session));

        $response->assertRedirect(route('admin.sessions.index'));
        $this->assertDatabaseMissing('course_sessions', ['id' => $session->id]);
    }
}
