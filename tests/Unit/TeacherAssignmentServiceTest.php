<?php

namespace Tests\Unit;

use App\Models\Course;
use App\Models\User;
use App\Services\TeacherAssignmentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeacherAssignmentServiceTest extends TestCase
{
    use RefreshDatabase;

    private TeacherAssignmentService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new TeacherAssignmentService;
    }

    public function test_can_assign_teacher_to_course()
    {
        $course = Course::factory()->create();
        $teacher = User::factory()->create(['role' => 'teacher']);

        $this->service->assignTeacher($course, $teacher);

        $this->assertTrue($this->service->isTeacherAssigned($course, $teacher));
        $this->assertDatabaseHas('course_teacher', [
            'course_id' => $course->id,
            'teacher_id' => $teacher->id,
        ]);
    }

    public function test_cannot_assign_student_as_teacher()
    {
        $course = Course::factory()->create();
        $student = User::factory()->create(['role' => 'student']);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Only teachers can be assigned to courses.');

        $this->service->assignTeacher($course, $student);
    }

    public function test_can_remove_teacher_from_course()
    {
        $course = Course::factory()->create();
        $teacher = User::factory()->create(['role' => 'teacher']);

        // Assign first
        $course->teachers()->attach($teacher->id);
        $this->assertTrue($this->service->isTeacherAssigned($course, $teacher));

        // Then remove
        $this->service->removeTeacher($course, $teacher);

        $this->assertFalse($this->service->isTeacherAssigned($course, $teacher));
        $this->assertDatabaseMissing('course_teacher', [
            'course_id' => $course->id,
            'teacher_id' => $teacher->id,
        ]);
    }

    public function test_get_available_teachers_returns_only_teachers()
    {
        $teacher1 = User::factory()->create(['role' => 'teacher']);
        $teacher2 = User::factory()->create(['role' => 'teacher']);
        $student = User::factory()->create(['role' => 'student']);
        $admin = User::factory()->create(['role' => 'admin']);

        $availableTeachers = $this->service->getAvailableTeachers();

        $this->assertEquals(2, $availableTeachers->count());
        $this->assertTrue($availableTeachers->contains($teacher1));
        $this->assertTrue($availableTeachers->contains($teacher2));
        $this->assertFalse($availableTeachers->contains($student));
        $this->assertFalse($availableTeachers->contains($admin));
    }

    public function test_get_course_teachers_returns_assigned_teachers()
    {
        $course = Course::factory()->create();
        $teacher1 = User::factory()->create(['role' => 'teacher']);
        $teacher2 = User::factory()->create(['role' => 'teacher']);
        $teacher3 = User::factory()->create(['role' => 'teacher']);

        // Assign only teacher1 and teacher2
        $course->teachers()->attach([$teacher1->id, $teacher2->id]);

        $courseTeachers = $this->service->getCourseTeachers($course);

        $this->assertEquals(2, $courseTeachers->count());
        $this->assertTrue($courseTeachers->contains($teacher1));
        $this->assertTrue($courseTeachers->contains($teacher2));
        $this->assertFalse($courseTeachers->contains($teacher3));
    }

    public function test_get_teacher_courses_returns_courses_taught_by_teacher()
    {
        $teacher = User::factory()->create(['role' => 'teacher']);
        $course1 = Course::factory()->create();
        $course2 = Course::factory()->create();
        $course3 = Course::factory()->create();

        // Assign teacher to course1 and course2
        $teacher->courses()->attach([$course1->id, $course2->id]);

        $teacherCourses = $this->service->getTeacherCourses($teacher);

        $this->assertEquals(2, $teacherCourses->count());
        $this->assertTrue($teacherCourses->contains($course1));
        $this->assertTrue($teacherCourses->contains($course2));
        $this->assertFalse($teacherCourses->contains($course3));
    }

    public function test_is_teacher_assigned_returns_correct_boolean()
    {
        $course = Course::factory()->create();
        $assignedTeacher = User::factory()->create(['role' => 'teacher']);
        $unassignedTeacher = User::factory()->create(['role' => 'teacher']);

        $course->teachers()->attach($assignedTeacher->id);

        $this->assertTrue($this->service->isTeacherAssigned($course, $assignedTeacher));
        $this->assertFalse($this->service->isTeacherAssigned($course, $unassignedTeacher));
    }
}
