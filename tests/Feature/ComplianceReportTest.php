<?php

namespace Tests\Feature;

use App\Models\Area;
use App\Models\ComplianceEvidence;
use App\Models\ComplianceReport;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ComplianceReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_a_report_with_multiple_evidence_files(): void
    {
        Storage::fake('local_private');
        $admin = $this->userWithRole('admin');
        $area = Area::create(['code' => 'AREA-III', 'name' => 'Curriculum and Instruction', 'created_by' => $admin->id]);

        $this->actingAs($admin)->get(route('compliance-reports.create'))
            ->assertOk()
            ->assertSee('New Compliance Report');

        $response = $this->actingAs($admin)->post(route('compliance-reports.store'), [
            'area_id' => $area->id,
            'program' => 'BS Information Systems',
            'survey_visit' => '3rd Survey Visit',
            'recommendations' => [[
                'recommendation' => 'Implement a flexible learning system.',
                'action_taken' => 'Acquired and provisioned an LMS.',
                'compliance_percentage' => 95,
                'files' => [
                    UploadedFile::fake()->createWithContent('certificate.pdf', '%PDF-1.4 evidence'),
                    UploadedFile::fake()->createWithContent('proposal.pdf', '%PDF-1.4 evidence'),
                ],
            ]],
        ]);

        $response->assertSessionHasNoErrors();
    $response->assertStatus(302);
        $report = ComplianceReport::firstOrFail();
        $response->assertRedirect(route('compliance-reports.show', $report));
        $this->assertDatabaseHas('compliance_recommendations', ['compliance_report_id' => $report->id, 'compliance_percentage' => 95]);
        $this->assertSame(2, ComplianceEvidence::count());
        ComplianceEvidence::each(fn (ComplianceEvidence $evidence) => Storage::disk('local_private')->assertExists($evidence->file_path));

        $recommendation = $report->recommendations()->firstOrFail();
        $this->actingAs($admin)->put(route('compliance-reports.update', $report), [
            'area_id' => $area->id,
            'program' => 'BS Information Systems',
            'survey_visit' => '3rd Survey Visit',
            'recommendations' => [[
                'id' => $recommendation->id,
                'recommendation' => $recommendation->recommendation,
                'action_taken' => $recommendation->action_taken,
                'compliance_percentage' => 100,
                'files' => [UploadedFile::fake()->createWithContent('memo.pdf', '%PDF-1.4 evidence')],
            ]],
        ])->assertRedirect(route('compliance-reports.show', $report));

        $this->assertSame(3, ComplianceEvidence::count());
        $evidence = ComplianceEvidence::firstOrFail();
        $this->actingAs($admin)->get(route('compliance-reports.show', $report))->assertOk()->assertSee('COMPLIANCE REPORT');
        $this->actingAs($admin)->get(route('compliance-evidences.stream', $evidence))->assertOk();
        $this->actingAs($admin)->get(route('compliance-evidences.download', $evidence))->assertOk();
        $this->actingAs($admin)->delete(route('compliance-evidences.destroy', $evidence))->assertRedirect();
        Storage::disk('local_private')->assertMissing($evidence->file_path);
        $this->assertDatabaseMissing('compliance_evidences', ['id' => $evidence->id]);
    }

    public function test_accreditor_can_view_only_reports_for_assigned_areas(): void
    {
        $admin = $this->userWithRole('admin');
        $accreditor = $this->userWithRole('accreditor');
        $assignedArea = Area::create(['code' => 'AREA-I', 'name' => 'Assigned Area', 'created_by' => $admin->id]);
        $otherArea = Area::create(['code' => 'AREA-II', 'name' => 'Other Area', 'created_by' => $admin->id]);
        $assignedArea->users()->attach($accreditor, ['assignment_role' => 'accreditor', 'assigned_by' => $admin->id, 'assigned_at' => now()]);
        $visibleReport = ComplianceReport::create(['area_id' => $assignedArea->id, 'created_by' => $admin->id, 'updated_by' => $admin->id]);
        $hiddenReport = ComplianceReport::create(['area_id' => $otherArea->id, 'created_by' => $admin->id, 'updated_by' => $admin->id]);

        $this->actingAs($accreditor)->get(route('compliance-reports.index'))
            ->assertOk()
            ->assertSee('Assigned Area')
            ->assertDontSee('Other Area');
        $this->actingAs($accreditor)->get(route('compliance-reports.show', $visibleReport))->assertOk();
        $this->actingAs($accreditor)->get(route('compliance-reports.show', $hiddenReport))->assertForbidden();
        $this->actingAs($accreditor)->get(route('compliance-reports.create'))->assertForbidden();
    }

    private function userWithRole(string $role): User
    {
        Role::findOrCreate($role, 'web');
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }
}