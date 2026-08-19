<?php

namespace Tests\Feature;

use App\Models\Area;
use App\Models\ProgramPerformanceComplianceFile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ProgramPerformanceComplianceTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_manage_one_private_ppp_pdf_for_each_of_the_ten_areas(): void
    {
        Storage::fake('local_private');
        $admin = $this->userWithRole('admin');
        $areas = collect(['I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X'])->map(function (string $number) use ($admin) {
            return Area::create(['code' => "AREA-{$number}", 'name' => "Area {$number} Profile", 'created_by' => $admin->id]);
        });

        $this->actingAs($admin)->get(route('program-performance-compliance.index'))
            ->assertOk()
            ->assertSee('Area I Profile')
            ->assertSee('Area X Profile')
            ->assertSee('10 Areas');

        $area = $areas->first();
        $this->actingAs($admin)->post(route('program-performance-compliance.store', $area), [
            'file' => UploadedFile::fake()->createWithContent('area-i-profile.pdf', '%PDF-1.4 Area I Profile'),
        ])->assertRedirect();

        $file = ProgramPerformanceComplianceFile::firstOrFail();
        Storage::disk('local_private')->assertExists($file->file_path);
        $this->actingAs($admin)->get(route('program-performance-compliance.stream', $file))->assertOk();
        $this->actingAs($admin)->get(route('program-performance-compliance.download', $file))->assertOk();

        $previousPath = $file->file_path;
        $this->actingAs($admin)->put(route('program-performance-compliance.update', $file), [
            'file' => UploadedFile::fake()->createWithContent('area-i-revised.pdf', '%PDF-1.4 Revised Profile'),
        ])->assertRedirect();
        $file->refresh();
        $this->assertNotSame($previousPath, $file->file_path);
        Storage::disk('local_private')->assertMissing($previousPath);
        Storage::disk('local_private')->assertExists($file->file_path);

        $this->actingAs($admin)->delete(route('program-performance-compliance.destroy', $file))->assertRedirect();
        Storage::disk('local_private')->assertMissing($file->file_path);
        $this->assertDatabaseCount('program_performance_compliance_files', 0);
    }

    public function test_assigned_faculty_can_upload_but_accreditor_can_only_view_assigned_area_file(): void
    {
        Storage::fake('local_private');
        $admin = $this->userWithRole('admin');
        $faculty = $this->userWithRole('faculty');
        $accreditor = $this->userWithRole('accreditor');
        $area = Area::create(['code' => 'AREA-III', 'name' => 'Curriculum', 'created_by' => $admin->id]);
        $otherArea = Area::create(['code' => 'AREA-IV', 'name' => 'Support', 'created_by' => $admin->id]);
        $area->users()->attach($faculty, ['assignment_role' => 'member', 'assigned_by' => $admin->id, 'assigned_at' => now()]);
        $area->users()->attach($accreditor, ['assignment_role' => 'accreditor', 'assigned_by' => $admin->id, 'assigned_at' => now()]);

        $this->actingAs($faculty)->post(route('program-performance-compliance.store', $area), [
            'file' => UploadedFile::fake()->createWithContent('curriculum.pdf', '%PDF-1.4 Curriculum'),
        ])->assertRedirect();
        $file = ProgramPerformanceComplianceFile::firstOrFail();

        $this->actingAs($accreditor)->get(route('program-performance-compliance.stream', $file))->assertOk();
        $this->actingAs($accreditor)->get(route('program-performance-compliance.download', $file))->assertForbidden();
        $this->actingAs($accreditor)->put(route('program-performance-compliance.update', $file), [
            'file' => UploadedFile::fake()->createWithContent('blocked.pdf', '%PDF-1.4 blocked'),
        ])->assertForbidden();
        $this->actingAs($accreditor)->post(route('program-performance-compliance.store', $otherArea), [
            'file' => UploadedFile::fake()->createWithContent('blocked.pdf', '%PDF-1.4 blocked'),
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