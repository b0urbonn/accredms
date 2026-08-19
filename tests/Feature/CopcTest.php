<?php

namespace Tests\Feature;

use App\Models\CopcFile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CopcTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_upload_view_replace_and_delete_the_single_copc_pdf(): void
    {
        Storage::fake('local_private');
        $admin = $this->userWithRole('admin');

        $this->actingAs($admin)->get(route('copc.index'))
            ->assertOk()
            ->assertSee('No COPC file uploaded yet');

        $this->actingAs($admin)->post(route('copc.store'), [
            'file' => UploadedFile::fake()->createWithContent('copc.pdf', '%PDF-1.4 Certificate'),
        ])->assertRedirect();

        $copcFile = CopcFile::firstOrFail();
        Storage::disk('local_private')->assertExists($copcFile->file_path);
        $this->actingAs($admin)->get(route('copc.stream', $copcFile))->assertOk();
        $this->actingAs($admin)->get(route('copc.download', $copcFile))->assertOk();

        $previousPath = $copcFile->file_path;
        $this->actingAs($admin)->post(route('copc.store'), [
            'file' => UploadedFile::fake()->createWithContent('copc-replaced.pdf', '%PDF-1.4 Replacement'),
        ])->assertRedirect();
        $copcFile->refresh();
        $this->assertDatabaseCount('copc_files', 1);
        $this->assertNotSame($previousPath, $copcFile->file_path);
        Storage::disk('local_private')->assertMissing($previousPath);
        Storage::disk('local_private')->assertExists($copcFile->file_path);

        $this->actingAs($admin)->delete(route('copc.destroy', $copcFile))->assertRedirect();
        Storage::disk('local_private')->assertMissing($copcFile->file_path);
        $this->assertDatabaseCount('copc_files', 0);
    }

    public function test_non_admin_users_can_view_but_cannot_modify_copc(): void
    {
        Storage::fake('local_private');
        $admin = $this->userWithRole('admin');
        $faculty = $this->userWithRole('faculty');
        $accreditor = $this->userWithRole('accreditor');
        $this->actingAs($admin)->post(route('copc.store'), [
            'file' => UploadedFile::fake()->createWithContent('copc.pdf', '%PDF-1.4 Certificate'),
        ]);
        $copcFile = CopcFile::firstOrFail();

        $this->actingAs($faculty)->get(route('copc.index'))->assertOk()->assertDontSee('Replace PDF');
        $this->actingAs($faculty)->get(route('copc.stream', $copcFile))->assertOk();
        $this->actingAs($faculty)->get(route('copc.download', $copcFile))->assertOk();
        $this->actingAs($faculty)->post(route('copc.store'), ['file' => UploadedFile::fake()->createWithContent('blocked.pdf', '%PDF-1.4 Blocked')])->assertForbidden();
        $this->actingAs($faculty)->delete(route('copc.destroy', $copcFile))->assertForbidden();

        $this->actingAs($accreditor)->get(route('copc.index'))->assertOk()->assertDontSee('Replace PDF');
        $this->actingAs($accreditor)->get(route('copc.stream', $copcFile))->assertOk();
        $this->actingAs($accreditor)->get(route('copc.download', $copcFile))->assertForbidden();
        $this->actingAs($accreditor)->post(route('copc.store'), ['file' => UploadedFile::fake()->createWithContent('blocked.pdf', '%PDF-1.4 Blocked')])->assertForbidden();
    }

    private function userWithRole(string $role): User
    {
        Role::findOrCreate($role, 'web');
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }
}