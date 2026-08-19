<?php

namespace Tests\Feature;

use App\Models\Area;
use App\Models\AccreditorEvaluation;
use App\Models\Category;
use App\Models\Document;
use App\Models\DocumentRemark;
use App\Models\Parameter;
use App\Models\Subfolder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AccreditationDmsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed roles & categories
        $this->seed(\Database\Seeders\RoleAndPermissionSeeder::class);
        $this->seed(\Database\Seeders\CategorySeeder::class);
    }

    /** @test */
    public function admin_can_create_area_and_parameter_auto_generates_3_categories()
    {
        $adminRole = Role::where('name', 'admin')->first();
        $admin = User::factory()->create(['role_id' => $adminRole->id]);
        $admin->assignRole($adminRole);

        $this->actingAs($admin);

        // Create Area
        $response = $this->post(route('admin.areas.store'), [
            'code' => 'AREA-TEST',
            'name' => 'Test Area Name',
            'description' => 'Test Area Description',
        ]);

        $response->assertRedirect(route('admin.areas.index'));
        $this->assertDatabaseHas('areas', ['code' => 'AREA-TEST']);

        $area = Area::where('code', 'AREA-TEST')->first();

        // Create Parameter under Area
        $paramResponse = $this->post(route('admin.parameters.store', $area), [
            'code' => '1.1',
            'title' => 'Test Parameter Title',
        ]);

        $paramResponse->assertRedirect();
        $this->assertDatabaseHas('parameters', ['code' => '1.1', 'area_id' => $area->id]);

        $parameter = Parameter::where('code', '1.1')->first();

        // Verify ParameterObserver auto-created 3 parameter_categories
        $this->assertEquals(3, $parameter->parameterCategories()->count());
    }

    /** @test */
    public function assigned_faculty_can_create_and_delete_an_empty_parameter()
    {
        $adminRole = Role::where('name', 'admin')->first();
        $facultyRole = Role::where('name', 'faculty')->first();
        $admin = User::factory()->create(['role_id' => $adminRole->id]);
        $faculty = User::factory()->create(['role_id' => $facultyRole->id]);
        $faculty->assignRole($facultyRole);

        $area = Area::create(['code' => 'AREA-FAC-PARAM', 'name' => 'Faculty Parameter Area', 'created_by' => $admin->id]);
        $area->users()->attach($faculty->id, ['assignment_role' => 'member', 'assigned_by' => $admin->id]);

        $createResponse = $this->actingAs($faculty)->post(route('parameters.store', $area), [
            'code' => '1.1',
            'title' => 'Faculty-created Parameter',
        ]);

        $createResponse->assertRedirect();
        $parameter = Parameter::where('area_id', $area->id)->where('code', '1.1')->firstOrFail();
        $this->assertEquals(3, $parameter->parameterCategories()->count());

        $deleteResponse = $this->delete(route('parameters.destroy', $parameter));
        $deleteResponse->assertRedirect();
        $this->assertSoftDeleted('parameters', ['id' => $parameter->id]);
    }

    /** @test */
    public function faculty_cannot_manage_parameters_in_an_unassigned_area()
    {
        $facultyRole = Role::where('name', 'faculty')->first();
        $faculty = User::factory()->create(['role_id' => $facultyRole->id]);
        $faculty->assignRole($facultyRole);
        $area = Area::create(['code' => 'AREA-NO-ACCESS', 'name' => 'Unassigned Area']);
        $parameter = Parameter::create(['area_id' => $area->id, 'code' => '1.1', 'title' => 'Protected Parameter']);

        $this->actingAs($faculty)
            ->post(route('parameters.store', $area), ['code' => '1.2', 'title' => 'Unauthorized Parameter'])
            ->assertForbidden();

        $this->delete(route('parameters.destroy', $parameter))->assertForbidden();
        $this->assertDatabaseHas('parameters', ['id' => $parameter->id, 'deleted_at' => null]);
    }

    /** @test */
    public function assigned_faculty_can_update_a_parameter()
    {
        $adminRole = Role::where('name', 'admin')->first();
        $facultyRole = Role::where('name', 'faculty')->first();
        $admin = User::factory()->create(['role_id' => $adminRole->id]);
        $faculty = User::factory()->create(['role_id' => $facultyRole->id]);
        $faculty->assignRole($facultyRole);

        $area = Area::create(['code' => 'AREA-FAC-EDIT', 'name' => 'Faculty Edit Area', 'created_by' => $admin->id]);
        $area->users()->attach($faculty->id, ['assignment_role' => 'handler', 'assigned_by' => $admin->id]);
        $parameter = Parameter::create(['area_id' => $area->id, 'code' => '1.1', 'title' => 'Original Parameter']);

        $response = $this->actingAs($faculty)->put(route('parameters.update', $parameter), [
            'code' => '1.2',
            'title' => 'Updated Parameter',
            'description' => 'Updated description',
            'sort_order' => 2,
            'status' => 'active',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('parameters', [
            'id' => $parameter->id,
            'code' => '1.2',
            'title' => 'Updated Parameter',
            'sort_order' => 2,
        ]);
    }

    /** @test */
    public function duplicate_parameter_code_or_title_is_rejected_in_same_area()
    {
        $adminRole = Role::where('name', 'admin')->first();
        $admin = User::factory()->create(['role_id' => $adminRole->id]);
        $area = Area::create(['code' => 'AREA-DUP-PARAM', 'name' => 'Duplicate Param Area', 'created_by' => $admin->id]);

        Parameter::create(['area_id' => $area->id, 'code' => 'A', 'title' => 'Parameter A']);

        // Attempting to create duplicate code 'A' should fail validation
        $response1 = $this->actingAs($admin)->post(route('admin.parameters.store', $area), [
            'code' => 'A',
            'title' => 'New Title',
        ]);
        $response1->assertSessionHasErrors(['code']);

        // Attempting to create duplicate title 'Parameter A' should fail validation
        $response2 = $this->actingAs($admin)->post(route('admin.parameters.store', $area), [
            'code' => 'B',
            'title' => 'Parameter A',
        ]);
        $response2->assertSessionHasErrors(['title']);
    }

    /** @test */
    public function assigned_faculty_can_delete_another_users_document_in_their_area()
    {
        Storage::fake('local_private');

        $adminRole = Role::where('name', 'admin')->first();
        $facultyRole = Role::where('name', 'faculty')->first();
        $admin = User::factory()->create(['role_id' => $adminRole->id]);
        $faculty = User::factory()->create(['role_id' => $facultyRole->id]);
        $faculty->assignRole($facultyRole);

        $area = Area::create(['code' => 'AREA-FAC-DOCUMENT', 'name' => 'Faculty Document Area', 'created_by' => $admin->id]);
        $area->users()->attach($faculty->id, ['assignment_role' => 'member', 'assigned_by' => $admin->id]);
        $parameter = Parameter::create(['area_id' => $area->id, 'code' => '1.1', 'title' => 'Document Parameter']);
        $subfolder = Subfolder::create([
            'parameter_category_id' => $parameter->parameterCategories()->first()->id,
            'code' => 'S.1',
            'name' => 'Document Statement',
            'created_by' => $admin->id,
            'review_status' => 'under_review',
        ]);
        $path = 'documents/faculty-delete-test.pdf';
        Storage::disk('local_private')->put($path, 'PDF content');
        $document = Document::create([
            'subfolder_id' => $subfolder->id,
            'uploaded_by' => $admin->id,
            'original_filename' => 'admin-uploaded.pdf',
            'stored_filename' => 'faculty-delete-test.pdf',
            'disk' => 'local_private',
            'file_path' => $path,
            'file_size_bytes' => 11,
            'checksum_sha256' => hash('sha256', 'PDF content'),
        ]);

        $response = $this->actingAs($faculty)->delete(route('documents.destroy', $document));

        $response->assertRedirect();
        $this->assertDatabaseMissing('documents', ['id' => $document->id]);
        $this->assertDatabaseHas('subfolders', ['id' => $subfolder->id, 'review_status' => 'no_evidence']);
        Storage::disk('local_private')->assertMissing($path);
    }

    /** @test */
    public function faculty_dashboard_metrics_only_include_assigned_area_documents()
    {
        $facultyRole = Role::where('name', 'faculty')->first();
        $faculty = User::factory()->create(['role_id' => $facultyRole->id]);
        $faculty->assignRole($facultyRole);

        $assignedArea = Area::create(['code' => 'AREA-DASH-YES', 'name' => 'Assigned Dashboard Area']);
        $unassignedArea = Area::create(['code' => 'AREA-DASH-NO', 'name' => 'Unassigned Dashboard Area']);
        $assignedArea->users()->attach($faculty->id, ['assignment_role' => 'member']);

        foreach ([[$assignedArea, 'assigned.pdf', 2097152], [$unassignedArea, 'unassigned.pdf', 1048576]] as [$area, $filename, $size]) {
            $parameter = Parameter::create(['area_id' => $area->id, 'code' => '1.1', 'title' => 'Dashboard Parameter']);
            $subfolder = Subfolder::create([
                'parameter_category_id' => $parameter->parameterCategories()->first()->id,
                'code' => 'S.1',
                'name' => 'Dashboard Statement',
            ]);
            Document::create([
                'subfolder_id' => $subfolder->id,
                'uploaded_by' => $faculty->id,
                'original_filename' => $filename,
                'stored_filename' => $filename,
                'disk' => 'local_private',
                'file_path' => "documents/{$filename}",
                'file_size_bytes' => $size,
                'checksum_sha256' => hash('sha256', $filename),
            ]);
        }

        $response = $this->actingAs($faculty)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('Uploaded PDFs');
        $response->assertSee('Total Storage');
        $response->assertSee('2.00 MB');
        $response->assertDontSee('unassigned.pdf');
    }

    /** @test */
    public function official_area_report_includes_indicator_evidence_and_accreditor_findings()
    {
        $adminRole = Role::where('name', 'admin')->first();
        $accreditorRole = Role::where('name', 'accreditor')->first();
        $admin = User::factory()->create(['role_id' => $adminRole->id]);
        $admin->assignRole($adminRole);
        $accreditor = User::factory()->create(['role_id' => $accreditorRole->id]);
        $accreditor->assignRole($accreditorRole);

        $area = Area::create(['code' => 'AREA-REPORT', 'name' => 'Report Area', 'created_by' => $admin->id]);
        $parameter = Parameter::create(['area_id' => $area->id, 'code' => 'A', 'title' => 'Vision and Mission']);
        $subfolder = Subfolder::create([
            'parameter_category_id' => $parameter->parameterCategories()->first()->id,
            'code' => 'S.1',
            'name' => 'Vision evidence is documented.',
            'created_by' => $admin->id,
        ]);
        $document = Document::create([
            'subfolder_id' => $subfolder->id,
            'uploaded_by' => $admin->id,
            'original_filename' => 'vision.pdf',
            'stored_filename' => 'vision.pdf',
            'disk' => 'local_private',
            'file_path' => 'documents/vision.pdf',
            'file_size_bytes' => 1024,
            'checksum_sha256' => hash('sha256', 'vision.pdf'),
        ]);
        DocumentRemark::create([
            'document_id' => $document->id,
            'user_id' => $accreditor->id,
            'remark' => 'Include the latest board approval record.',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.areas.report', $area));

        $response->assertOk();
        $response->assertSee('Survey Indicator and Documentary Evidence Matrix');
        $response->assertSee('Vision evidence is documented.');
        $response->assertSee('vision.pdf');
        $response->assertSee('Accreditor Findings and Recommendations');
        $response->assertSee('Include the latest board approval record.');
    }

    /** @test */
    public function assigned_accreditor_can_rate_an_indicator_and_assigned_users_can_view_the_evaluation_report()
    {
        $adminRole = Role::where('name', 'admin')->first();
        $facultyRole = Role::where('name', 'faculty')->first();
        $accreditorRole = Role::where('name', 'accreditor')->first();
        $admin = User::factory()->create(['role_id' => $adminRole->id]);
        $admin->assignRole($adminRole);
        $faculty = User::factory()->create(['role_id' => $facultyRole->id]);
        $faculty->assignRole($facultyRole);
        $accreditor = User::factory()->create(['role_id' => $accreditorRole->id]);
        $accreditor->assignRole($accreditorRole);

        $area = Area::create(['code' => 'AREA-EVALUATION', 'name' => 'Evaluation Area', 'created_by' => $admin->id]);
        $area->users()->attach($faculty->id, ['assignment_role' => 'member', 'assigned_by' => $admin->id]);
        $area->users()->attach($accreditor->id, ['assignment_role' => 'accreditor', 'assigned_by' => $admin->id]);
        $parameter = Parameter::create(['area_id' => $area->id, 'code' => 'A', 'title' => 'Survey Parameter']);
        $statement = Subfolder::create([
            'parameter_category_id' => $parameter->parameterCategories()->first()->id,
            'code' => 'S.1',
            'name' => 'The survey indicator is evaluated.',
            'created_by' => $admin->id,
        ]);

        $rateResponse = $this->actingAs($accreditor)->put(route('accreditor.evaluations.store', $statement), [
            'rating' => 4,
            'compliance_result' => 'complied',
            'evaluation' => 'Evidence is complete; retain the current documentation practice.',
        ]);

        $rateResponse->assertRedirect();
        $this->assertDatabaseHas('accreditor_evaluations', [
            'subfolder_id' => $statement->id,
            'user_id' => $accreditor->id,
            'rating' => 4,
        ]);
        $this->assertEquals(1, AccreditorEvaluation::count());

        $this->actingAs($faculty)
            ->get(route('accreditor.evaluation_report', $area))
            ->assertOk()
            ->assertSee('Accreditor Evaluation Report')
            ->assertSee('Evidence is complete; retain the current documentation practice.')
            ->assertSee('4.00');

        $this->actingAs($admin)
            ->get(route('accreditor.evaluation_report', $area))
            ->assertOk()
            ->assertSee('Area Mean: 4.00');
    }

    /** @test */
    public function accreditor_rates_a_multi_file_statement_only_once()
    {
        $accreditorRole = Role::where('name', 'accreditor')->first();
        $accreditor = User::factory()->create(['role_id' => $accreditorRole->id]);
        $accreditor->assignRole($accreditorRole);
        $area = Area::create(['code' => 'AREA-ONE-RATING', 'name' => 'One Rating Area']);
        $area->users()->attach($accreditor->id, ['assignment_role' => 'accreditor']);
        $parameter = Parameter::create(['area_id' => $area->id, 'code' => 'A', 'title' => 'Multi-file Parameter']);
        $statement = Subfolder::create(['parameter_category_id' => $parameter->parameterCategories()->first()->id, 'code' => 'S.1', 'name' => 'Multi-file statement']);

        foreach (['first.pdf', 'second.pdf'] as $filename) {
            Document::create(['subfolder_id' => $statement->id, 'uploaded_by' => $accreditor->id, 'original_filename' => $filename, 'stored_filename' => $filename, 'disk' => 'local_private', 'file_path' => "documents/{$filename}", 'file_size_bytes' => 1, 'checksum_sha256' => hash('sha256', $filename)]);
        }

        $this->actingAs($accreditor)->put(route('accreditor.evaluations.store', $statement), ['rating' => 3, 'compliance_result' => 'partially_complied', 'evaluation' => 'Initial item evaluation.'])->assertRedirect();
        $this->put(route('accreditor.evaluations.store', $statement), ['rating' => 5, 'compliance_result' => 'complied', 'evaluation' => 'Updated item evaluation.'])->assertRedirect();

        $this->assertDatabaseCount('accreditor_evaluations', 1);
        $this->assertDatabaseHas('accreditor_evaluations', ['subfolder_id' => $statement->id, 'user_id' => $accreditor->id, 'rating' => 5, 'evaluation' => 'Updated item evaluation.']);
    }

    /** @test */
    public function assigned_faculty_can_submit_evidence_and_resubmit_after_revision()
    {
        $adminRole = Role::where('name', 'admin')->first();
        $facultyRole = Role::where('name', 'faculty')->first();
        $admin = User::factory()->create(['role_id' => $adminRole->id]);
        $admin->assignRole($adminRole);
        $faculty = User::factory()->create(['role_id' => $facultyRole->id]);
        $faculty->assignRole($facultyRole);
        $area = Area::create(['code' => 'AREA-SUBMIT', 'name' => 'Evidence Submission Area', 'created_by' => $admin->id]);
        $area->users()->attach($faculty->id, ['assignment_role' => 'member', 'assigned_by' => $admin->id]);
        $parameter = Parameter::create(['area_id' => $area->id, 'code' => 'A', 'title' => 'Submission Parameter']);
        $statement = Subfolder::create(['parameter_category_id' => $parameter->parameterCategories()->first()->id, 'code' => 'S.1', 'name' => 'Submission statement']);
        Document::create(['subfolder_id' => $statement->id, 'uploaded_by' => $faculty->id, 'original_filename' => 'evidence.pdf', 'stored_filename' => 'evidence.pdf', 'disk' => 'local_private', 'file_path' => 'documents/evidence.pdf', 'file_size_bytes' => 1, 'checksum_sha256' => hash('sha256', 'evidence.pdf')]);

        $this->actingAs($faculty)
            ->put(route('subfolders.evidence_status.update', $statement), ['evidence_status' => 'submitted'])
            ->assertRedirect();
        $this->assertDatabaseHas('subfolders', ['id' => $statement->id, 'evidence_status' => 'submitted']);

        $statement->update(['evidence_status' => 'needs_revision']);
        $this->put(route('subfolders.evidence_status.update', $statement), ['evidence_status' => 'submitted'])->assertRedirect();
        $this->assertDatabaseHas('subfolders', ['id' => $statement->id, 'evidence_status' => 'submitted']);
        $this->assertDatabaseHas('audit_logs', ['action' => 'update_evidence_status']);
    }

    /** @test */
    public function faculty_cannot_submit_evidence_without_a_document()
    {
        $facultyRole = Role::where('name', 'faculty')->first();
        $faculty = User::factory()->create(['role_id' => $facultyRole->id]);
        $faculty->assignRole($facultyRole);
        $area = Area::create(['code' => 'AREA-NO-EVIDENCE', 'name' => 'No Evidence Area']);
        $area->users()->attach($faculty->id, ['assignment_role' => 'member']);
        $parameter = Parameter::create(['area_id' => $area->id, 'code' => 'A', 'title' => 'No Evidence Parameter']);
        $statement = Subfolder::create(['parameter_category_id' => $parameter->parameterCategories()->first()->id, 'code' => 'S.1', 'name' => 'Empty statement']);

        $this->actingAs($faculty)
            ->from(route('accreditor.show_area', $area))
            ->put(route('subfolders.evidence_status.update', $statement), ['evidence_status' => 'submitted'])
            ->assertRedirect(route('accreditor.show_area', $area))
            ->assertSessionHasErrors('evidence_status');
        $this->assertDatabaseHas('subfolders', ['id' => $statement->id, 'evidence_status' => 'draft']);
    }

    /** @test */
    public function assigned_accreditor_can_review_evidence_but_an_unassigned_accreditor_cannot()
    {
        $accreditorRole = Role::where('name', 'accreditor')->first();
        $assignedAccreditor = User::factory()->create(['role_id' => $accreditorRole->id]);
        $assignedAccreditor->assignRole($accreditorRole);
        $unassignedAccreditor = User::factory()->create(['role_id' => $accreditorRole->id]);
        $unassignedAccreditor->assignRole($accreditorRole);
        $area = Area::create(['code' => 'AREA-REVIEW', 'name' => 'Evidence Review Area']);
        $area->users()->attach($assignedAccreditor->id, ['assignment_role' => 'accreditor']);
        $parameter = Parameter::create(['area_id' => $area->id, 'code' => 'A', 'title' => 'Review Parameter']);
        $statement = Subfolder::create(['parameter_category_id' => $parameter->parameterCategories()->first()->id, 'code' => 'S.1', 'name' => 'Review statement', 'evidence_status' => 'submitted']);

        $this->actingAs($assignedAccreditor)
            ->put(route('subfolders.evidence_status.update', $statement), ['evidence_status' => 'under_review'])
            ->assertRedirect();
        $this->put(route('subfolders.evidence_status.update', $statement), ['evidence_status' => 'needs_revision'])->assertRedirect();
        $statement->refresh();
        $this->assertSame('needs_revision', $statement->evidence_status);

        $this->actingAs($unassignedAccreditor)
            ->put(route('subfolders.evidence_status.update', $statement), ['evidence_status' => 'approved'])
            ->assertForbidden();
        $this->assertDatabaseHas('subfolders', ['id' => $statement->id, 'evidence_status' => 'needs_revision']);
    }

    /** @test */
    public function admin_can_view_evidence_status_but_cannot_change_it()
    {
        $adminRole = Role::where('name', 'admin')->first();
        $admin = User::factory()->create(['role_id' => $adminRole->id]);
        $admin->assignRole($adminRole);
        $area = Area::create(['code' => 'AREA-STATUS-VIEW', 'name' => 'Status View Area', 'created_by' => $admin->id]);
        $parameter = Parameter::create(['area_id' => $area->id, 'code' => 'A', 'title' => 'Status Parameter']);
        $statement = Subfolder::create([
            'parameter_category_id' => $parameter->parameterCategories()->first()->id,
            'code' => 'S.1',
            'name' => 'Approved statement',
            'evidence_status' => 'approved',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.areas.show', $area))
            ->assertOk()
            ->assertSee('approved');

        $this->put(route('subfolders.evidence_status.update', $statement), ['evidence_status' => 'submitted'])
            ->assertForbidden();
        $this->assertDatabaseHas('subfolders', ['id' => $statement->id, 'evidence_status' => 'approved']);
    }

    /** @test */
    public function unassigned_accreditor_cannot_rate_or_view_an_area_evaluation_report()
    {
        $accreditorRole = Role::where('name', 'accreditor')->first();
        $accreditor = User::factory()->create(['role_id' => $accreditorRole->id]);
        $accreditor->assignRole($accreditorRole);
        $area = Area::create(['code' => 'AREA-EVAL-LOCKED', 'name' => 'Locked Evaluation Area']);
        $parameter = Parameter::create(['area_id' => $area->id, 'code' => 'A', 'title' => 'Locked Parameter']);
        $statement = Subfolder::create([
            'parameter_category_id' => $parameter->parameterCategories()->first()->id,
            'code' => 'S.1',
            'name' => 'Locked indicator',
        ]);

        $this->actingAs($accreditor)
            ->get(route('accreditor.evaluation_report', $area))
            ->assertForbidden();

        $this->put(route('accreditor.evaluations.store', $statement), [
            'rating' => 4,
            'evaluation' => 'Unauthorized evaluation.',
        ])->assertForbidden();
        $this->assertDatabaseCount('accreditor_evaluations', 0);
    }

    /** @test */
    public function assigned_accreditor_can_request_additional_documents_and_assigned_users_are_notified()
    {
        [$admin, $faculty, $accreditor, $area, $statement] = $this->additionalDocumentWorkflowContext();

        $this->actingAs($accreditor)
            ->post(route('accreditor.additional_document_requests.store', $statement), [
                'requested_documents' => 'Current-year board resolution and signed attendance sheet.',
                'remarks' => 'Please upload the missing current-year evidence.',
                'due_date' => now()->addWeek()->toDateString(),
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('additional_document_requests', [
            'subfolder_id' => $statement->id,
            'requested_by' => $accreditor->id,
            'status' => 'open',
        ]);
        $this->assertDatabaseHas('subfolders', ['id' => $statement->id, 'review_status' => 'additional_documents_requested']);
        $this->assertDatabaseHas('notifications', ['notifiable_id' => $faculty->id]);
        $this->assertDatabaseHas('notifications', ['notifiable_id' => $admin->id]);
    }

    /** @test */
    public function unassigned_user_cannot_request_additional_documents()
    {
        [, , , , $statement] = $this->additionalDocumentWorkflowContext();
        $accreditorRole = Role::where('name', 'accreditor')->first();
        $unassignedAccreditor = User::factory()->create(['role_id' => $accreditorRole->id]);
        $unassignedAccreditor->assignRole($accreditorRole);

        $this->actingAs($unassignedAccreditor)
            ->post(route('accreditor.additional_document_requests.store', $statement), [
                'remarks' => 'Unauthorized request.',
            ])
            ->assertForbidden();
        $this->assertDatabaseCount('additional_document_requests', 0);
    }

    /** @test */
    public function assigned_faculty_can_resubmit_requested_evidence()
    {
        Storage::fake('local_private');
        [, $faculty, $accreditor, , $statement] = $this->additionalDocumentWorkflowContext();
        $this->actingAs($accreditor)->post(route('accreditor.additional_document_requests.store', $statement), ['remarks' => 'Upload the signed resolution.']);

        $pdf = UploadedFile::fake()->createWithContent('resolution.pdf', "%PDF-1.4\n%%EOF");
        $this->actingAs($faculty)
            ->post(route('documents.store', $statement), ['files' => [$pdf]])
            ->assertRedirect();

        $this->assertDatabaseHas('documents', ['subfolder_id' => $statement->id, 'original_filename' => 'resolution.pdf']);
    }

    /** @test */
    public function resubmission_updates_request_statement_and_recipients()
    {
        Storage::fake('local_private');
        [$admin, $faculty, $accreditor, , $statement] = $this->additionalDocumentWorkflowContext();
        $this->actingAs($accreditor)->post(route('accreditor.additional_document_requests.store', $statement), ['remarks' => 'Upload the signed resolution.']);

        $pdf = UploadedFile::fake()->createWithContent('resolution.pdf', "%PDF-1.4\n%%EOF");
        $this->actingAs($faculty)->post(route('documents.store', $statement), ['files' => [$pdf]])->assertRedirect();

        $this->assertDatabaseHas('additional_document_requests', ['subfolder_id' => $statement->id, 'status' => 'resubmitted']);
        $this->assertDatabaseHas('subfolders', ['id' => $statement->id, 'review_status' => 'resubmitted']);
        $this->assertDatabaseHas('notifications', ['notifiable_id' => $accreditor->id]);
        $this->assertDatabaseHas('notifications', ['notifiable_id' => $admin->id]);
    }

    /** @test */
    public function administrator_can_comply_with_requested_evidence()
    {
        Storage::fake('local_private');
        [$admin, , $accreditor, , $statement] = $this->additionalDocumentWorkflowContext();
        $this->actingAs($accreditor)->post(route('accreditor.additional_document_requests.store', $statement), ['remarks' => 'Upload the signed resolution.']);

        $pdf = UploadedFile::fake()->createWithContent('admin-resolution.pdf', "%PDF-1.4\n%%EOF");
        $this->actingAs($admin)->post(route('documents.store', $statement), ['files' => [$pdf]])->assertRedirect();

        $this->assertDatabaseHas('documents', ['subfolder_id' => $statement->id, 'original_filename' => 'admin-resolution.pdf']);
        $this->assertDatabaseHas('additional_document_requests', ['subfolder_id' => $statement->id, 'status' => 'resubmitted']);
        $this->assertDatabaseHas('subfolders', ['id' => $statement->id, 'review_status' => 'resubmitted']);
    }

    /** @test */
    public function assigned_accreditor_can_evaluate_with_a_compliance_result()
    {
        [, , $accreditor, , $statement] = $this->additionalDocumentWorkflowContext();

        $this->actingAs($accreditor)
            ->put(route('accreditor.evaluations.store', $statement), [
                'rating' => 4,
                'compliance_result' => 'partially_complied',
                'evaluation' => 'Evidence meets most requirements; retain the additional signed annex.',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('accreditor_evaluations', [
            'subfolder_id' => $statement->id,
            'user_id' => $accreditor->id,
            'compliance_result' => 'partially_complied',
        ]);
        $this->assertDatabaseHas('subfolders', ['id' => $statement->id, 'review_status' => 'evaluated']);
    }

    /** @test */
    public function assigned_accreditor_can_save_a_document_finding_with_indicator_rating_and_evaluation()
    {
        $accreditorRole = Role::where('name', 'accreditor')->first();
        $accreditor = User::factory()->create(['role_id' => $accreditorRole->id]);
        $accreditor->assignRole($accreditorRole);
        $area = Area::create(['code' => 'AREA-DOC-EVAL', 'name' => 'Document Evaluation Area']);
        $area->users()->attach($accreditor->id, ['assignment_role' => 'accreditor']);
        $parameter = Parameter::create(['area_id' => $area->id, 'code' => 'A', 'title' => 'Document Evaluation Parameter']);
        $statement = Subfolder::create([
            'parameter_category_id' => $parameter->parameterCategories()->first()->id,
            'code' => 'S.1',
            'name' => 'Document evaluation indicator',
        ]);
        $document = Document::create([
            'subfolder_id' => $statement->id,
            'uploaded_by' => $accreditor->id,
            'original_filename' => 'evidence.pdf',
            'stored_filename' => 'evidence.pdf',
            'disk' => 'local_private',
            'file_path' => 'documents/evidence.pdf',
            'file_size_bytes' => 1024,
            'checksum_sha256' => hash('sha256', 'evidence.pdf'),
        ]);

        $response = $this->actingAs($accreditor)->post(route('documents.remarks.store', $document), [
            'rating' => 4,
            'remark' => 'The submitted PDF supports the indicator.',
            'evaluation' => 'Add the current-year approval attachment.',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('document_remarks', [
            'document_id' => $document->id,
            'user_id' => $accreditor->id,
            'remark' => 'The submitted PDF supports the indicator.',
        ]);
        $this->assertDatabaseHas('accreditor_evaluations', [
            'subfolder_id' => $statement->id,
            'user_id' => $accreditor->id,
            'rating' => 4,
            'evaluation' => 'Add the current-year approval attachment.',
        ]);

        $this->get(route('accreditor.show_area', $area))
            ->assertOk();
    }

    /** @test */
    public function accreditor_can_edit_only_their_own_document_evaluation()
    {
        $accreditorRole = Role::where('name', 'accreditor')->first();
        $firstAccreditor = User::factory()->create(['role_id' => $accreditorRole->id]);
        $firstAccreditor->assignRole($accreditorRole);
        $secondAccreditor = User::factory()->create(['role_id' => $accreditorRole->id]);
        $secondAccreditor->assignRole($accreditorRole);
        $area = Area::create(['code' => 'AREA-EDIT-EVAL', 'name' => 'Edit Evaluation Area']);
        $area->users()->attach($firstAccreditor->id, ['assignment_role' => 'accreditor']);
        $area->users()->attach($secondAccreditor->id, ['assignment_role' => 'accreditor']);
        $parameter = Parameter::create(['area_id' => $area->id, 'code' => 'A', 'title' => 'Edit Evaluation Parameter']);
        $statement = Subfolder::create([
            'parameter_category_id' => $parameter->parameterCategories()->first()->id,
            'code' => 'S.1',
            'name' => 'Editable indicator',
        ]);
        $document = Document::create([
            'subfolder_id' => $statement->id,
            'uploaded_by' => $firstAccreditor->id,
            'original_filename' => 'editable.pdf',
            'stored_filename' => 'editable.pdf',
            'disk' => 'local_private',
            'file_path' => 'documents/editable.pdf',
            'file_size_bytes' => 1024,
            'checksum_sha256' => hash('sha256', 'editable.pdf'),
        ]);
        $remark = DocumentRemark::create([
            'document_id' => $document->id,
            'user_id' => $firstAccreditor->id,
            'remark' => 'Initial finding.',
        ]);
        AccreditorEvaluation::create([
            'subfolder_id' => $statement->id,
            'user_id' => $firstAccreditor->id,
            'rating' => 3,
            'evaluation' => 'Initial evaluation.',
        ]);

        $updateResponse = $this->actingAs($firstAccreditor)->put(route('documents.remarks.update', [$document, $remark]), [
            'rating' => 5,
            'remark' => 'Updated finding.',
            'evaluation' => 'Updated recommendation.',
        ]);

        $updateResponse->assertRedirect();
        $this->assertDatabaseHas('document_remarks', ['id' => $remark->id, 'remark' => 'Updated finding.']);
        $this->assertDatabaseHas('accreditor_evaluations', [
            'subfolder_id' => $statement->id,
            'user_id' => $firstAccreditor->id,
            'rating' => 5,
            'evaluation' => 'Updated recommendation.',
        ]);

        $this->actingAs($secondAccreditor)
            ->put(route('documents.remarks.update', [$document, $remark]), [
                'rating' => 0,
                'remark' => 'Unauthorized update.',
            ])
            ->assertForbidden();
        $this->assertDatabaseMissing('document_remarks', ['id' => $remark->id, 'remark' => 'Unauthorized update.']);
    }

    /** @test */
    public function faculty_can_upload_valid_pdf_document()
    {
        Storage::fake('local_private');

        $adminRole = Role::where('name', 'admin')->first();
        $facultyRole = Role::where('name', 'faculty')->first();

        $admin = User::factory()->create(['role_id' => $adminRole->id]);
        $admin->assignRole($adminRole);

        $faculty = User::factory()->create(['role_id' => $facultyRole->id]);
        $faculty->assignRole($facultyRole);

        // Setup Area, Parameter & Subfolder
        $area = Area::create(['code' => 'AREA-FAC', 'name' => 'Faculty Area', 'created_by' => $admin->id]);
        $parameter = Parameter::create(['area_id' => $area->id, 'code' => '1.1', 'title' => 'Title']);
        $paramCat = $parameter->parameterCategories()->first();

        // Assign area to faculty
        $area->users()->attach($faculty->id, ['assignment_role' => 'handler', 'assigned_by' => $admin->id]);

        $subfolder = Subfolder::create([
            'parameter_category_id' => $paramCat->id,
            'name' => 'Reports Subfolder',
            'created_by' => $faculty->id,
        ]);

        $this->actingAs($faculty);

        // Generate mock valid PDF file (%PDF- magic bytes header)
        $pdfContent = "%PDF-1.4\n1 0 obj\n<< /Type /Catalog >>\nendobj\ntrailer\n<< /Root 1 0 R >>\n%%EOF";
        $fakeFile = UploadedFile::fake()->createWithContent('document.pdf', $pdfContent);

        $uploadResponse = $this->post(route('documents.store', $subfolder), [
            'files' => [$fakeFile],
        ]);

        $uploadResponse->assertRedirect();
        $this->assertDatabaseHas('documents', [
            'subfolder_id' => $subfolder->id,
            'original_filename' => 'document.pdf',
            'uploaded_by' => $faculty->id,
        ]);

        $document = Document::where('original_filename', 'document.pdf')->first();
        $this->assertTrue(Storage::disk('local_private')->exists($document->file_path));

        // Test streaming route
        $streamResponse = $this->get(route('documents.stream', $document));
        $streamResponse->assertStatus(200);
        $streamResponse->assertHeader('Content-Type', 'application/pdf');
    }

    /** @test */
    public function unauthorized_user_cannot_access_unassigned_area_documents()
    {
        Storage::fake('local_private');

        $facultyRole = Role::where('name', 'faculty')->first();
        $faculty1 = User::factory()->create(['role_id' => $facultyRole->id]);
        $faculty1->assignRole($facultyRole);

        $faculty2 = User::factory()->create(['role_id' => $facultyRole->id]);
        $faculty2->assignRole($facultyRole);

        $area = Area::create(['code' => 'AREA-SEC', 'name' => 'Secure Area']);
        $parameter = Parameter::create(['area_id' => $area->id, 'code' => '1.1', 'title' => 'Title']);
        $paramCat = $parameter->parameterCategories()->first();

        // Assign area ONLY to faculty1
        $area->users()->attach($faculty1->id, ['assignment_role' => 'handler']);

        $subfolder = Subfolder::create(['parameter_category_id' => $paramCat->id, 'name' => 'Sub']);
        $document = Document::create([
            'subfolder_id' => $subfolder->id,
            'uploaded_by' => $faculty1->id,
            'original_filename' => 'secret.pdf',
            'stored_filename' => 'uuid.pdf',
            'disk' => 'local_private',
            'file_path' => 'documents/fake.pdf',
            'file_size_bytes' => 100,
            'checksum_sha256' => 'abc',
        ]);

        // Faculty2 attempts streaming -> expected 403
        $this->actingAs($faculty2);
        $response = $this->get(route('documents.stream', $document));
        $response->assertStatus(403);
    }

    /** @test */
    public function admin_cannot_delete_an_area_that_contains_parameters()
    {
        $adminRole = Role::where('name', 'admin')->first();
        $admin = User::factory()->create(['role_id' => $adminRole->id]);
        $admin->assignRole($adminRole);

        $area = Area::create(['code' => 'AREA-LOCKED', 'name' => 'Protected Area', 'created_by' => $admin->id]);
        Parameter::create(['area_id' => $area->id, 'code' => '1.1', 'title' => 'Existing Parameter']);

        $response = $this->actingAs($admin)->delete(route('admin.areas.destroy', $area));

        $response->assertRedirect(route('admin.areas.index'));
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('areas', ['id' => $area->id]);
    }

    /** @test */
    public function admin_can_only_delete_an_empty_parameter()
    {
        $adminRole = Role::where('name', 'admin')->first();
        $admin = User::factory()->create(['role_id' => $adminRole->id]);
        $admin->assignRole($adminRole);

        $area = Area::create(['code' => 'AREA-PARAMETER-DELETE', 'name' => 'Parameter Delete Area', 'created_by' => $admin->id]);
        $emptyParameter = Parameter::create(['area_id' => $area->id, 'code' => 'EMPTY', 'title' => 'Empty Parameter']);
        $populatedParameter = Parameter::create(['area_id' => $area->id, 'code' => 'DATA', 'title' => 'Populated Parameter']);
        $category = $populatedParameter->parameterCategories()->first();

        Subfolder::create([
            'parameter_category_id' => $category->id,
            'code' => 'S.1',
            'name' => 'Existing Statement',
            'created_by' => $admin->id,
        ]);

        $blockedResponse = $this->actingAs($admin)->delete(route('admin.parameters.destroy', $populatedParameter));
        $blockedResponse->assertRedirect();
        $blockedResponse->assertSessionHas('error');
        $this->assertDatabaseHas('parameters', ['id' => $populatedParameter->id, 'deleted_at' => null]);

        $deleteResponse = $this->delete(route('admin.parameters.destroy', $emptyParameter));
        $deleteResponse->assertRedirect();
        $this->assertDatabaseMissing('parameters', ['id' => $emptyParameter->id, 'deleted_at' => null]);
    }

    /** @test */
    public function subfolder_cannot_be_deleted_when_a_descendant_has_a_document()
    {
        $adminRole = Role::where('name', 'admin')->first();
        $admin = User::factory()->create(['role_id' => $adminRole->id]);
        $admin->assignRole($adminRole);
        $area = Area::create(['code' => 'AREA-SUBFOLDER-FILE', 'name' => 'Subfolder File Area', 'created_by' => $admin->id]);
        $parameter = Parameter::create(['area_id' => $area->id, 'code' => 'FILE', 'title' => 'File Parameter']);
        $parent = Subfolder::create(['parameter_category_id' => $parameter->parameterCategories()->first()->id, 'code' => 'S.1', 'name' => 'Parent', 'created_by' => $admin->id]);
        $child = Subfolder::create(['parameter_category_id' => $parent->parameter_category_id, 'parent_id' => $parent->id, 'code' => 'S.1.1', 'name' => 'Child', 'created_by' => $admin->id]);
        Document::create(['subfolder_id' => $child->id, 'uploaded_by' => $admin->id, 'original_filename' => 'child.pdf', 'stored_filename' => 'child.pdf', 'disk' => 'local_private', 'file_path' => 'documents/child.pdf', 'file_size_bytes' => 1, 'checksum_sha256' => hash('sha256', 'child')]);

        $response = $this->actingAs($admin)->delete(route('subfolders.destroy', $parent));

        $response->assertRedirect()->assertSessionHas('error');
        $this->assertDatabaseHas('subfolders', ['id' => $parent->id, 'deleted_at' => null]);
    }

    /** @test */
    public function admin_can_delete_an_empty_subfolder_and_its_empty_descendants()
    {
        $adminRole = Role::where('name', 'admin')->first();
        $admin = User::factory()->create(['role_id' => $adminRole->id]);
        $admin->assignRole($adminRole);
        $area = Area::create(['code' => 'AREA-SUBFOLDER-EMPTY', 'name' => 'Empty Subfolder Area', 'created_by' => $admin->id]);
        $parameter = Parameter::create(['area_id' => $area->id, 'code' => 'EMPTY-SUB', 'title' => 'Empty Subfolder Parameter']);
        $parent = Subfolder::create(['parameter_category_id' => $parameter->parameterCategories()->first()->id, 'code' => 'S.2', 'name' => 'Parent', 'created_by' => $admin->id]);
        $child = Subfolder::create(['parameter_category_id' => $parent->parameter_category_id, 'parent_id' => $parent->id, 'code' => 'S.2.1', 'name' => 'Child', 'created_by' => $admin->id]);

        $response = $this->actingAs($admin)->delete(route('subfolders.destroy', $parent));

        $response->assertRedirect()->assertSessionHas('success');
        $this->assertDatabaseMissing('subfolders', ['id' => $parent->id, 'deleted_at' => null]);
        $this->assertDatabaseMissing('subfolders', ['id' => $child->id, 'deleted_at' => null]);
    }

    /** @test */
    public function inactive_area_cannot_be_opened_even_by_an_admin()
    {
        $adminRole = Role::where('name', 'admin')->first();
        $admin = User::factory()->create(['role_id' => $adminRole->id]);
        $admin->assignRole($adminRole);

        $area = Area::create([
            'code' => 'AREA-INACTIVE',
            'name' => 'Inactive Area',
            'status' => 'inactive',
            'created_by' => $admin->id,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.areas.show', $area));

        $response->assertForbidden();
    }

    /** @test */
    public function admin_can_reuse_the_code_of_a_permanently_deleted_empty_area()
    {
        $adminRole = Role::where('name', 'admin')->first();
        $admin = User::factory()->create(['role_id' => $adminRole->id]);
        $admin->assignRole($adminRole);

        $area = Area::create(['code' => 'AREA-REUSE', 'name' => 'Old Empty Area', 'created_by' => $admin->id]);

        $deleteResponse = $this->actingAs($admin)->delete(route('admin.areas.destroy', $area));
        $deleteResponse->assertRedirect(route('admin.areas.index'));
        $this->assertDatabaseMissing('areas', ['id' => $area->id]);

        $createResponse = $this->post(route('admin.areas.store'), [
            'code' => 'AREA-REUSE',
            'name' => 'Replacement Area',
        ]);

        $createResponse->assertRedirect(route('admin.areas.index'));
        $this->assertDatabaseHas('areas', [
            'code' => 'AREA-REUSE',
            'name' => 'Replacement Area',
        ]);
    }

    /** @test */
    public function creating_the_code_of_a_deleted_area_with_parameters_restores_its_data()
    {
        $adminRole = Role::where('name', 'admin')->first();
        $admin = User::factory()->create(['role_id' => $adminRole->id]);
        $admin->assignRole($adminRole);

        $area = Area::create(['code' => 'AREA-RESTORE', 'name' => 'Protected Area', 'created_by' => $admin->id]);
        Parameter::create(['area_id' => $area->id, 'code' => '1.1', 'title' => 'Existing Parameter']);
        $area->delete();

        $response = $this->actingAs($admin)->post(route('admin.areas.store'), [
            'code' => 'AREA-RESTORE',
            'name' => 'Replacement Attempt',
        ]);

        $response->assertRedirect(route('admin.areas.index'));
        $response->assertSessionHas('warning');
        $this->assertDatabaseHas('areas', ['id' => $area->id, 'deleted_at' => null]);
        $this->assertDatabaseHas('parameters', ['area_id' => $area->id, 'code' => '1.1']);
    }

    /** @test */
    public function admin_must_create_users_with_a_strong_password()
    {
        $adminRole = Role::where('name', 'admin')->first();
        $admin = User::factory()->create(['role_id' => $adminRole->id]);
        $admin->assignRole($adminRole);

        $weakResponse = $this->actingAs($admin)->post(route('admin.users.store'), [
            'name' => 'Weak Password User',
            'email' => 'weak@example.test',
            'password' => 'password',
            'role' => 'faculty',
        ]);

        $weakResponse->assertSessionHasErrors('password');

        $strongResponse = $this->post(route('admin.users.store'), [
            'name' => 'Strong Password User',
            'email' => 'strong@example.test',
            'password' => 'Strong#2026',
            'role' => 'faculty',
        ]);

        $strongResponse->assertRedirect(route('admin.users.index'));
        $this->assertDatabaseHas('users', ['email' => 'strong@example.test']);
    }

    /** @test */
    public function admin_can_update_a_user_but_cannot_weaken_own_access()
    {
        $adminRole = Role::where('name', 'admin')->first();
        $facultyRole = Role::where('name', 'faculty')->first();
        $admin = User::factory()->create(['role_id' => $adminRole->id, 'status' => 'active']);
        $admin->assignRole($adminRole);
        $faculty = User::factory()->create(['role_id' => $facultyRole->id, 'status' => 'active']);
        $faculty->assignRole($facultyRole);

        $updateResponse = $this->actingAs($admin)->put(route('admin.users.update', $faculty), [
            'employee_id' => 'FAC-UPDATED',
            'name' => 'Updated Faculty',
            'email' => 'updated.faculty@example.test',
            'role' => 'accreditor',
            'status' => 'inactive',
            'password' => 'Updated#2026',
        ]);

        $updateResponse->assertRedirect(route('admin.users.index'));
        $this->assertDatabaseHas('users', [
            'id' => $faculty->id,
            'email' => 'updated.faculty@example.test',
            'status' => 'inactive',
        ]);
        $this->assertTrue(User::find($faculty->id)->hasRole('accreditor'));

        $selfUpdateResponse = $this->put(route('admin.users.update', $admin), [
            'name' => $admin->name,
            'email' => $admin->email,
            'role' => 'faculty',
            'status' => 'inactive',
        ]);

        $selfUpdateResponse->assertSessionHasErrors('account');
        $this->assertDatabaseHas('users', ['id' => $admin->id, 'status' => 'active', 'role_id' => $adminRole->id]);
    }

    /** @test */
    public function user_update_rejects_weak_password_and_duplicate_identity_values()
    {
        $adminRole = Role::where('name', 'admin')->first();
        $facultyRole = Role::where('name', 'faculty')->first();
        $admin = User::factory()->create(['role_id' => $adminRole->id]);
        $admin->assignRole($adminRole);
        $firstUser = User::factory()->create(['employee_id' => 'FAC-UNIQUE', 'role_id' => $facultyRole->id]);
        $firstUser->assignRole($facultyRole);
        $secondUser = User::factory()->create(['employee_id' => 'FAC-SECOND', 'role_id' => $facultyRole->id]);
        $secondUser->assignRole($facultyRole);

        $response = $this->actingAs($admin)->put(route('admin.users.update', $secondUser), [
            'employee_id' => 'FAC-UNIQUE',
            'name' => $secondUser->name,
            'email' => $firstUser->email,
            'role' => 'faculty',
            'status' => 'active',
            'password' => 'weakpass',
        ]);

        $response->assertSessionHasErrors(['employee_id', 'email', 'password']);
    }

    private function additionalDocumentWorkflowContext(): array
    {
        $adminRole = Role::where('name', 'admin')->first();
        $facultyRole = Role::where('name', 'faculty')->first();
        $accreditorRole = Role::where('name', 'accreditor')->first();

        $admin = User::factory()->create(['role_id' => $adminRole->id]);
        $admin->assignRole($adminRole);
        $faculty = User::factory()->create(['role_id' => $facultyRole->id]);
        $faculty->assignRole($facultyRole);
        $accreditor = User::factory()->create(['role_id' => $accreditorRole->id]);
        $accreditor->assignRole($accreditorRole);

        $area = Area::create(['code' => 'AREA-ADDITIONAL-DOCS', 'name' => 'Additional Documents Area', 'created_by' => $admin->id]);
        $area->users()->attach($faculty->id, ['assignment_role' => 'handler', 'assigned_by' => $admin->id]);
        $area->users()->attach($accreditor->id, ['assignment_role' => 'accreditor', 'assigned_by' => $admin->id]);
        $parameter = Parameter::create(['area_id' => $area->id, 'code' => 'A', 'title' => 'Additional Documents Parameter']);
        $statement = Subfolder::create([
            'parameter_category_id' => $parameter->parameterCategories()->first()->id,
            'code' => 'S.1',
            'name' => 'Additional documents statement',
            'documents_needed' => 'Current-year board resolution and signed attendance sheet.',
        ]);

        return [$admin, $faculty, $accreditor, $area, $statement];
    }
}
