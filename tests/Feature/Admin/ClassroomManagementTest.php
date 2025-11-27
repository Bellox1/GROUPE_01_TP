<?php

namespace Tests\Feature\Admin;

use App\Models\Classroom;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClassroomManagementTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsAdmin()
    {
        $user = User::factory()->admin()->create();
        return $this->actingAs($user);
    }

    public function test_admin_can_view_classrooms_index()
    {
        $classrooms = Classroom::factory()->count(3)->create();

        $response = $this->actingAsAdmin()->get(route('admin.classrooms.index'));

        $response->assertStatus(200);
        $response->assertViewHas('classrooms');
        foreach ($classrooms as $classroom) {
            $response->assertSee($classroom->name);
        }
    }

    public function test_admin_can_view_create_classroom_page()
    {
        $response = $this->actingAsAdmin()->get(route('admin.classrooms.create'));
        $response->assertStatus(200);
    }

    public function test_admin_can_create_classroom()
    {
        $response = $this->actingAsAdmin()->post(route('admin.classrooms.store'), [
            'name' => 'New Room',
            'capacity' => 30,
            'type' => 'ClassRoom',
        ]);

        $response->assertRedirect(route('admin.classrooms.index'));
        $this->assertDatabaseHas('classrooms', [
            'name' => 'New Room',
            'capacity' => 30,
            'type' => 'ClassRoom',
        ]);
    }

    public function test_admin_can_view_edit_classroom_page()
    {
        $classroom = Classroom::factory()->create();
        $response = $this->actingAsAdmin()->get(route('admin.classrooms.edit', $classroom));
        $response->assertStatus(200);
    }

    public function test_admin_can_update_classroom()
    {
        $classroom = Classroom::factory()->create();

        $response = $this->actingAsAdmin()->put(route('admin.classrooms.update', $classroom), [
            'name' => 'Updated Room',
            'capacity' => 50,
            'type' => 'Amphitheater',
        ]);

        $response->assertRedirect(route('admin.classrooms.index'));
        $this->assertDatabaseHas('classrooms', [
            'id' => $classroom->id,
            'name' => 'Updated Room',
            'capacity' => 50,
            'type' => 'Amphitheater',
        ]);
    }

    public function test_admin_can_delete_classroom()
    {
        $classroom = Classroom::factory()->create();

        $response = $this->actingAsAdmin()->delete(route('admin.classrooms.destroy', $classroom));

        $response->assertRedirect(route('admin.classrooms.index'));
        $this->assertDatabaseMissing('classrooms', [
            'id' => $classroom->id,
        ]);
    }
}
