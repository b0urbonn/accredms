<?php

namespace Tests\Feature;

use App\Models\AdditionalDocumentRequest;
use App\Models\Area;
use App\Models\Parameter;
use App\Models\Subfolder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AccreditorRequestAreaTabsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleAndPermissionSeeder::class);
        $this->seed(\Database\Seeders\CategorySeeder::class);
    }

    public function test_accreditor_dashboard_shows_request_area_tabs_with_counts_for_own_requests(): void
    {
        $admin = $this->userWithRole('admin');
        $accreditor = $this->userWithRole('accreditor');
        $otherAccreditor = $this->userWithRole('accreditor');
        $areaOne = Area::create(['code' => 'AREA-I', 'name' => 'First Area', 'created_by' => $admin->id]);
        $areaTwo = Area::create(['code' => 'AREA-II', 'name' => 'Second Area', 'created_by' => $admin->id]);
        $areaOne->users()->attach($accreditor, ['assignment_role' => 'accreditor', 'assigned_by' => $admin->id, 'assigned_at' => now()]);
        $areaTwo->users()->attach($accreditor, ['assignment_role' => 'accreditor', 'assigned_by' => $admin->id, 'assigned_at' => now()]);

        $statementOne = $this->statementFor($areaOne, 'S.1');
        $statementTwo = $this->statementFor($areaTwo, 'S.2');
        AdditionalDocumentRequest::create(['subfolder_id' => $statementOne->id, 'requested_by' => $accreditor->id, 'remarks' => 'First request', 'status' => 'open']);
        AdditionalDocumentRequest::create(['subfolder_id' => $statementOne->id, 'requested_by' => $accreditor->id, 'remarks' => 'Second request', 'status' => 'fulfilled']);
        AdditionalDocumentRequest::create(['subfolder_id' => $statementTwo->id, 'requested_by' => $accreditor->id, 'remarks' => 'Third request', 'status' => 'open']);
        AdditionalDocumentRequest::create(['subfolder_id' => $statementTwo->id, 'requested_by' => $otherAccreditor->id, 'remarks' => 'Hidden request', 'status' => 'open']);

        $this->actingAs($accreditor)->get(route('dashboard'))
            ->assertOk()
            ->assertSee('AREA-I')
            ->assertSee('AREA-II')
            ->assertSee('>2<', false)
            ->assertSee('>1<', false)
            ->assertDontSee('Hidden request');
    }

    private function statementFor(Area $area, string $code): Subfolder
    {
        $parameter = Parameter::create(['area_id' => $area->id, 'code' => '1.1', 'title' => 'Test Parameter']);

        return Subfolder::create([
            'parameter_category_id' => $parameter->parameterCategories()->firstOrFail()->id,
            'code' => $code,
            'name' => "Statement {$code}",
        ]);
    }

    private function userWithRole(string $role): User
    {
        $roleModel = Role::findOrCreate($role, 'web');
        $user = User::factory()->create();
        $user->assignRole($roleModel);

        return $user;
    }
}