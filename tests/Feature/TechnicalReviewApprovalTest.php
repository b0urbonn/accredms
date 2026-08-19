<?php

namespace Tests\Feature;

use App\Models\Area;
use App\Models\TechnicalReviewApproval;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class TechnicalReviewApprovalTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_upload_view_and_delete_reports(): void
    {
        Storage::fake('local_private');
        $admin = $this->userWithRole('admin');
        $area = Area::create(['code' => 'AREA-I', 'name' => 'Vision, Mission, Goals']);

        // Admin access and upload single file
        $this->actingAs($admin)->get(route('technical-review-approval.index'))
            ->assertOk()
            ->assertSee('Technical Review');

        $this->actingAs($admin)->post(route('technical-review-approval.store'), [
            'area_id' => $area->id,
            'category' => 'technical_review',
            'file' => UploadedFile::fake()->create('report1.pdf', 100, 'application/pdf'),
        ])->assertRedirect();

        $this->assertDatabaseHas('technical_review_approvals', [
            'original_filename' => 'report1.pdf',
            'category' => 'technical_review',
            'area_id' => $area->id,
        ]);

        // Admin upload multiple files
        $this->actingAs($admin)->post(route('technical-review-approval.store'), [
            'category' => 'board_approval',
            'files' => [
                UploadedFile::fake()->create('resolution1.pdf', 120, 'application/pdf'),
                UploadedFile::fake()->create('resolution2.pdf', 150, 'application/pdf'),
            ],
        ])->assertRedirect();

        $this->assertDatabaseCount('technical_review_approvals', 3);

        $firstReport = TechnicalReviewApproval::first();
        $this->actingAs($admin)->get(route('technical-review-approval.stream', $firstReport))->assertOk();
        $this->actingAs($admin)->get(route('technical-review-approval.download', $firstReport))->assertOk();

        // Delete report file
        $this->actingAs($admin)->delete(route('technical-review-approval.destroy', $firstReport))->assertRedirect();
        $this->assertSoftDeleted('technical_review_approvals', ['id' => $firstReport->id]);
    }

    public function test_faculty_and_accreditor_can_only_view_and_download_reports_not_upload_or_delete(): void
    {
        Storage::fake('local_private');
        $admin = $this->userWithRole('admin');
        $faculty = $this->userWithRole('faculty');
        $accreditor = $this->userWithRole('accreditor');

        $this->actingAs($admin)->post(route('technical-review-approval.store'), [
            'category' => 'general',
            'file' => UploadedFile::fake()->create('admin_report.pdf', 100, 'application/pdf'),
        ]);

        $report = TechnicalReviewApproval::firstOrFail();

        // Faculty checks
        $this->actingAs($faculty)->get(route('technical-review-approval.index'))->assertOk()->assertDontSee('Upload Reports');
        $this->actingAs($faculty)->get(route('technical-review-approval.stream', $report))->assertOk();
        $this->actingAs($faculty)->get(route('technical-review-approval.download', $report))->assertOk();
        $this->actingAs($faculty)->post(route('technical-review-approval.store'), [
            'file' => UploadedFile::fake()->create('faculty_blocked.pdf', 100, 'application/pdf'),
        ])->assertForbidden();
        $this->actingAs($faculty)->delete(route('technical-review-approval.destroy', $report))->assertForbidden();

        // Accreditor checks
        $this->actingAs($accreditor)->get(route('technical-review-approval.index'))->assertOk()->assertDontSee('Upload Reports');
        $this->actingAs($accreditor)->get(route('technical-review-approval.stream', $report))->assertOk();
        $this->actingAs($accreditor)->get(route('technical-review-approval.download', $report))->assertOk();
        $this->actingAs($accreditor)->post(route('technical-review-approval.store'), [
            'file' => UploadedFile::fake()->create('accreditor_blocked.pdf', 100, 'application/pdf'),
        ])->assertForbidden();
        $this->actingAs($accreditor)->delete(route('technical-review-approval.destroy', $report))->assertForbidden();
    }

    private function userWithRole(string $role): User
    {
        Role::findOrCreate($role, 'web');
        $user = User::factory()->create(['role_id' => $role === 'admin' ? 1 : ($role === 'faculty' ? 2 : 3)]);
        $user->assignRole($role);

        return $user;
    }
}
