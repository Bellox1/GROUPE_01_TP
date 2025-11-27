<?php

namespace Tests\Feature\Admin;

use App\Models\Course;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeacherAssignmentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create(['role' => 'admin']));
    }

    public function test_admin_can_view_teacher_assignment_form()
    {
        $course = Course::factory()->create();
        $assignedTeacher = User::factory()->create(['role' => 'teacher']);
        $availableTeacher = User::factory()->create(['role' => 'teacher']);

        $course->teachers()->attach($assignedTeacher->id);

        $response = $this->get(route('admin.courses.teachers.edit', $course));

        $response->assertStatus(200);
        $response->assertSee($assignedTeacher->name);
        $response->assertSee($availableTeacher->name);
        $response->assertSee('Enseignants Assignés');
    }

    public function test_admin_can_assign_teacher_to_course()
    {
        $course = Course::factory()->create();
        $teacher = User::factory()->create(['role' => 'teacher']);

        $response = $this->post(route('admin.courses.teachers.assign', $course), [
            'teacher_id' => $teacher->id,
        ]);

        $response->assertRedirect(route('admin.courses.teachers.edit', $course));
        $this->assertDatabaseHas('course_teacher', [
            'course_id' => $course->id,
            'teacher_id' => $teacher->id,
        ]);
    }

    public function test_admin_cannot_assign_student_as_teacher()
    {
        $course = Course::factory()->create();
        $student = User::factory()->create(['role' => 'student']);

        $response = $this->post(route('admin.courses.teachers.assign', $course), [
            'teacher_id' => $student->id,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error', 'Only teachers can be assigned to courses.');
        $this->assertDatabaseMissing('course_teacher', [
            'course_id' => $course->id,
            'teacher_id' => $student->id,
        ]);
    }

    public function test_admin_can_remove_teacher_from_course()
    {
        $course = Course::factory()->create();
        $teacher = User::factory()->create(['role' => 'teacher']);

        $course->teachers()->attach($teacher->id);

        $response = $this->delete(route('admin.courses.teachers.remove', [$course, $teacher]));

        $response->assertRedirect(route('admin.courses.teachers.edit', $course));
        $this->assertDatabaseMissing('course_teacher', [
            'course_id' => $course->id,
            'teacher_id' => $teacher->id,
        ]);
    }

    public function test_teacher_id_is_required_for_assignment()
    {
        $course = Course::factory()->create();

        $response = $this->post(route('admin.courses.teachers.assign', $course), []);

        $response->assertSessionHasErrors(['teacher_id']);
    }

    public function test_teacher_id_must_exist_in_users_table()
    {
        $course = Course::factory()->create();

        $response = $this->post(route('admin.courses.teachers.assign', $course), [
            'teacher_id' => 999,
        ]);

        $response->assertSessionHasErrors(['teacher_id']);
    }

    public function test_same_teacher_cannot_be_assigned_twice_to_same_course()
    {
        $course = Course::factory()->create();
        $teacher = User::factory()->create(['role' => 'teacher']);

        // Assign teacher first time
        $course->teachers()->attach($teacher->id);

        // Try to assign again - should succeed but not create duplicate
        $response = $this->post(route('admin.courses.teachers.assign', $course), [
            'teacher_id' => $teacher->id,
        ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();
        // Should still only have one record
        $this->assertEquals(1, $course->teachers()->where('teacher_id', $teacher->id)->count());
    }
}
