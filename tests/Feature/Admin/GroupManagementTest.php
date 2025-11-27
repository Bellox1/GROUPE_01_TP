<?php

namespace Tests\Feature\Admin;

use App\Models\Group;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GroupManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create(['role' => 'admin']));
    }

    public function test_admin_can_view_groups_list()
    {
        $groups = Group::factory()->count(3)->create();

        $response = $this->get(route('admin.groups.index'));

        $response->assertStatus(200);
        foreach ($groups as $group) {
            $response->assertSee($group->name);
        }
    }

    public function test_admin_can_view_create_group_form()
    {
        $response = $this->get(route('admin.groups.create'));

        $response->assertStatus(200);
        $response->assertSee('Créer un Nouveau Groupe');
    }

    public function test_admin_can_create_group()
    {
        $groupData = [
            'name' => 'Test Group A',
        ];

        $response = $this->post(route('admin.groups.store'), $groupData);

        $response->assertRedirect(route('admin.groups.index'));
        $this->assertDatabaseHas('groups', $groupData);
    }

    public function test_group_name_is_required()
    {
        $response = $this->post(route('admin.groups.store'), []);

        $response->assertSessionHasErrors(['name']);
    }

    public function test_group_name_must_be_unique()
    {
        Group::factory()->create(['name' => 'Unique Group']);

        $response = $this->post(route('admin.groups.store'), [
            'name' => 'Unique Group',
        ]);

        $response->assertSessionHasErrors(['name']);
    }

    public function test_admin_can_view_edit_group_form()
    {
        $group = Group::factory()->create();

        $response = $this->get(route('admin.groups.edit', $group));

        $response->assertStatus(200);
        $response->assertSee('Modifier le Groupe');
        $response->assertSee($group->name);
    }

    public function test_admin_can_update_group()
    {
        $group = Group::factory()->create(['name' => 'Old Name']);
        $updatedData = ['name' => 'Updated Name'];

        $response = $this->put(route('admin.groups.update', $group), $updatedData);

        $response->assertRedirect(route('admin.groups.index'));
        $this->assertDatabaseHas('groups', $updatedData);
        $this->assertDatabaseMissing('groups', ['name' => 'Old Name']);
    }

    public function test_admin_can_delete_group()
    {
        $group = Group::factory()->create();

        $response = $this->delete(route('admin.groups.destroy', $group));

        $response->assertRedirect(route('admin.groups.index'));
        $this->assertDatabaseMissing('groups', ['id' => $group->id]);
    }

    public function test_groups_list_shows_student_count()
    {
        $group = Group::factory()->create();
        $students = User::factory()->count(5)->create(['role' => 'student']);
        $group->students()->attach($students->pluck('id'));

        $response = $this->get(route('admin.groups.index'));

        $response->assertStatus(200);
        $response->assertSee('5');
    }

    public function test_group_update_ignores_itself_for_unique_validation()
    {
        $group = Group::factory()->create(['name' => 'Original Name']);

        $response = $this->put(route('admin.groups.update', $group), [
            'name' => 'Original Name',
        ]);

        $response->assertRedirect(route('admin.groups.index'));
        $response->assertSessionHasNoErrors();
    }
}
