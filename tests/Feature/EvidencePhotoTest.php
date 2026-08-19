<?php

namespace Tests\Feature;

use App\Models\Area;
use App\Models\EvidencePhoto;
use App\Models\Parameter;
use App\Models\Subfolder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class EvidencePhotoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\RoleAndPermissionSeeder::class);
        $this->seed(\Database\Seeders\CategorySeeder::class);
    }

    public function test_assigned_faculty_can_capture_stream_and_display_photo_evidence(): void
    {
        Storage::fake('local_private');

        $adminRole = Role::where('name', 'admin')->firstOrFail();
        $facultyRole = Role::where('name', 'faculty')->firstOrFail();
        $admin = User::factory()->create(['role_id' => $adminRole->id]);
        $faculty = User::factory()->create(['role_id' => $facultyRole->id]);
        $faculty->assignRole($facultyRole);

        $area = Area::create([
            'code' => 'AREA-PHOTO',
            'name' => 'Photo Evidence Area',
            'created_by' => $admin->id,
        ]);
        $area->users()->attach($faculty->id, [
            'assignment_role' => 'member',
            'assigned_by' => $admin->id,
        ]);
        $parameter = Parameter::create([
            'area_id' => $area->id,
            'code' => 'A',
            'title' => 'Photo Evidence Parameter',
        ]);
        $subfolder = Subfolder::create([
            'parameter_category_id' => $parameter->parameterCategories()->firstOrFail()->id,
            'code' => 'I.1',
            'name' => 'Photo Evidence Statement',
            'documents_needed' => "Meeting minutes\nAction photos",
            'created_by' => $admin->id,
        ]);
        $response = $this->actingAs($faculty)->post(route('evidence_photos.store', $subfolder), [
            'photos' => [
                UploadedFile::fake()->image('committee-meeting-1.jpg', 800, 600),
                UploadedFile::fake()->image('committee-meeting-2.jpg', 600, 1000),
            ],
            'checklist_item' => 'Action photos',
            'caption' => 'Committee meeting',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('evidence_photos', [
            'subfolder_id' => $subfolder->id,
            'uploaded_by' => $faculty->id,
            'checklist_item' => 'Action photos',
            'caption' => 'Committee meeting',
        ]);
        $this->assertSame(['Action photos'], $subfolder->fresh()->completed_checklist_array);

        $this->actingAs($faculty)->post(route('evidence_photos.store', $subfolder), [
            'photos' => [UploadedFile::fake()->image('meeting-minutes.jpg', 800, 600)],
            'checklist_item' => 'Meeting minutes',
        ])->assertRedirect();

        $this->assertEqualsCanonicalizing(['Action photos', 'Meeting minutes'], $subfolder->fresh()->completed_checklist_array);

        $photo = EvidencePhoto::firstOrFail();
        Storage::disk('local_private')->assertExists($photo->file_path);
        $storedImage = getimagesizefromstring(Storage::disk('local_private')->get($photo->file_path));
        $this->assertSame('image/jpeg', $photo->mime_type);
        $this->assertLessThanOrEqual(2000, max($storedImage[0], $storedImage[1]));
        $storedOrientations = EvidencePhoto::all()->map(function (EvidencePhoto $storedPhoto): array {
            $dimensions = getimagesizefromstring(Storage::disk('local_private')->get($storedPhoto->file_path));

            return [$dimensions[0], $dimensions[1]];
        });
        $this->assertTrue($storedOrientations->contains(fn (array $dimensions) => $dimensions[0] > $dimensions[1]));
        $this->assertTrue($storedOrientations->contains(fn (array $dimensions) => $dimensions[1] > $dimensions[0]));

        $this->actingAs($faculty)
            ->get(route('evidence_photos.pdf', $photo))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf')
            ->assertHeader('Content-Disposition', 'inline; filename="i1-photo-evidence.pdf"');

        $this->actingAs($faculty)
            ->get(route('accreditor.show_area', $area))
            ->assertOk()
            ->assertSee('All Evidences Complete (2/2)')
            ->assertSee('3 image(s)')
            ->assertSee('View PDF')
            ->assertSee('Photo evidence')
            ->assertDontSee('View Photo Report');
    }
}
