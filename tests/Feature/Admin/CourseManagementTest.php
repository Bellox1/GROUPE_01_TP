<?php

namespace Tests\Feature\Admin;

use App\Models\Course;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CourseManagementTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsAdmin()
    {
        $user = User::factory()->admin()->create();
        return $this->actingAs($user);
    }

    public function test_admin_can_view_courses_index()
    {
        $courses = Course::factory()->count(3)->create();

        $response = $this->actingAsAdmin()->get(route('admin.courses.index'));

        $response->assertStatus(200);
        $response->assertViewHas('courses');
        foreach ($courses as $course) {
            $response->assertSee($course->name);
        }
    }

    public function test_admin_can_view_create_course_page()
    {
        $response = $this->actingAsAdmin()->get(route('admin.courses.create'));
        $response->assertStatus(200);
    }

    public function test_admin_can_create_course()
    {
        $response = $this->actingAsAdmin()->post(route('admin.courses.store'), [
            'code' => 'CS101',
            'name' => 'Intro to CS',
            'description' => 'Basics of Computer Science',
            'credits' => 3,
        ]);

        $response->assertRedirect(route('admin.courses.index'));
        $this->assertDatabaseHas('courses', [
            'code' => 'CS101',
            'name' => 'Intro to CS',
            'credits' => 3,
        ]);
    }

    public function test_admin_can_view_edit_course_page()
    {
        $course = Course::factory()->create();
        $response = $this->actingAsAdmin()->get(route('admin.courses.edit', $course));
        $response->assertStatus(200);
    }

    public function test_admin_can_update_course()
    {
        $course = Course::factory()->create();

        $response = $this->actingAsAdmin()->put(route('admin.courses.update', $course), [
            'code' => 'CS102',
            'name' => 'Advanced CS',
            'description' => 'Advanced topics',
            'credits' => 4,
        ]);

        $response->assertRedirect(route('admin.courses.index'));
        $this->assertDatabaseHas('courses', [
            'id' => $course->id,
            'code' => 'CS102',
            'name' => 'Advanced CS',
            'credits' => 4,
        ]);
    }

    public function test_admin_can_delete_course()
    {
        $course = Course::factory()->create();

        $response = $this->actingAsAdmin()->delete(route('admin.courses.destroy', $course));

        $response->assertRedirect(route('admin.courses.index'));
        $this->assertDatabaseMissing('courses', [
            'id' => $course->id,
        ]);
    }
}
