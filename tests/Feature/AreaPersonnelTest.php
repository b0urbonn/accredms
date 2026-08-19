<?php

namespace Tests\Feature;

use App\Models\Area;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AreaPersonnelTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_assign_chairman_and_members_when_creating_an_area_and_they_display_on_area_cards(): void
    {
        $admin = $this->userWithRole('admin');
        $chairman = $this->userWithRole('faculty');
        $member = $this->userWithRole('faculty');

        $this->actingAs($admin)->post(route('admin.areas.store'), [
            'code' => 'AREA-PERSONNEL',
            'name' => 'Personnel Assignment Area',
            'chairman_id' => $chairman->id,
            'member_ids' => [$member->id],
        ])->assertRedirect(route('admin.areas.index'));

        $area = Area::where('code', 'AREA-PERSONNEL')->firstOrFail();
        $this->assertDatabaseHas('area_user', ['area_id' => $area->id, 'user_id' => $chairman->id, 'assignment_role' => 'handler']);
        $this->assertDatabaseHas('area_user', ['area_id' => $area->id, 'user_id' => $member->id, 'assignment_role' => 'member']);

        $this->actingAs($admin)->get(route('accreditor.browse'))
            ->assertOk()
            ->assertSee('Chairman:')
            ->assertSee($chairman->name)
            ->assertSee('Members:')
            ->assertSee($member->name);
    }

    public function test_non_admin_cannot_create_an_area_or_assign_personnel(): void
    {
        $faculty = $this->userWithRole('faculty');
        $chairman = $this->userWithRole('faculty');

        $this->actingAs($faculty)->post(route('admin.areas.store'), [
            'code' => 'AREA-BLOCKED',
            'name' => 'Blocked Area',
            'chairman_id' => $chairman->id,
        ])->assertForbidden();
    }

    private function userWithRole(string $role): User
    {
        Role::findOrCreate($role, 'web');
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }
}