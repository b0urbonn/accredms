<?php

use App\Http\Controllers\Admin\AreaController;
use App\Http\Controllers\Admin\AssignmentController;
use App\Http\Controllers\Admin\ParameterController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Accreditor\BrowseController;
use App\Http\Controllers\Accreditor\AdditionalDocumentRequestController;
use App\Http\Controllers\Accreditor\EvaluationController;
use App\Http\Controllers\Accreditor\SupplementalEvidenceReviewController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentStreamController;
use App\Http\Controllers\EvidencePhotoStreamController;
use App\Http\Controllers\EvidenceStatusController;
use App\Http\Controllers\ComplianceReportController;
use App\Http\Controllers\CopcController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProgramPerformanceComplianceController;
use App\Http\Controllers\TechnicalReviewApprovalController;
use App\Http\Controllers\Faculty\DocumentController;
use App\Http\Controllers\Faculty\EvidencePhotoController;
use App\Http\Controllers\Faculty\SubfolderController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - SUC Accreditation Document Management System (ADMS)
|--------------------------------------------------------------------------
*/

// Guest Routes
Route::middleware('guest')->group(function () {
    Route::get('/', [LoginController::class, 'showLoginForm'])->name('login');
    Route::get('/login', [LoginController::class, 'showLoginForm']);
    Route::post('/login', [LoginController::class, 'login'])->name('login.post');
});

// Authenticated Routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/notifications/unread', [NotificationController::class, 'unread'])->name('notifications.unread');
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markRead'])->name('notifications.read');

    Route::resource('compliance-reports', ComplianceReportController::class);
    Route::get('compliance-evidences/{evidence}/stream', [ComplianceReportController::class, 'streamEvidence'])->name('compliance-evidences.stream');
    Route::get('compliance-evidences/{evidence}/download', [ComplianceReportController::class, 'downloadEvidence'])->name('compliance-evidences.download');
    Route::delete('compliance-evidences/{evidence}', [ComplianceReportController::class, 'destroyEvidence'])->name('compliance-evidences.destroy');

    Route::get('copc', [CopcController::class, 'index'])->name('copc.index');
    Route::post('copc', [CopcController::class, 'store'])->name('copc.store');
    Route::get('copc/{copcFile}/stream', [CopcController::class, 'stream'])->name('copc.stream');
    Route::get('copc/{copcFile}/download', [CopcController::class, 'download'])->name('copc.download');
    Route::delete('copc/{copcFile}', [CopcController::class, 'destroy'])->name('copc.destroy');

    Route::get('program-performance-compliance', [ProgramPerformanceComplianceController::class, 'index'])->name('program-performance-compliance.index');
    Route::post('program-performance-compliance/areas/{area}', [ProgramPerformanceComplianceController::class, 'store'])->name('program-performance-compliance.store');
    Route::put('program-performance-compliance/files/{programPerformanceComplianceFile}', [ProgramPerformanceComplianceController::class, 'update'])->name('program-performance-compliance.update');
    Route::get('program-performance-compliance/files/{programPerformanceComplianceFile}/stream', [ProgramPerformanceComplianceController::class, 'stream'])->name('program-performance-compliance.stream');
    Route::get('program-performance-compliance/files/{programPerformanceComplianceFile}/download', [ProgramPerformanceComplianceController::class, 'download'])->name('program-performance-compliance.download');
    Route::delete('program-performance-compliance/files/{programPerformanceComplianceFile}', [ProgramPerformanceComplianceController::class, 'destroy'])->name('program-performance-compliance.destroy');

    // Technical Review & Board Approval Routes
    Route::get('technical-review-approval', [TechnicalReviewApprovalController::class, 'index'])->name('technical-review-approval.index');
    Route::post('technical-review-approval', [TechnicalReviewApprovalController::class, 'store'])->name('technical-review-approval.store');
    Route::get('technical-review-approval/{technicalReviewApproval}/stream', [TechnicalReviewApprovalController::class, 'stream'])->name('technical-review-approval.stream');
    Route::get('technical-review-approval/{technicalReviewApproval}/download', [TechnicalReviewApprovalController::class, 'download'])->name('technical-review-approval.download');
    Route::delete('technical-review-approval/{technicalReviewApproval}', [TechnicalReviewApprovalController::class, 'destroy'])->name('technical-review-approval.destroy');

    // Legacy Route Resource Aliases (Redirecting to unified Technical Review & Approval)
    Route::get('technical-reports', fn () => redirect()->route('technical-review-approval.index'))->name('technical-reports.index');
    Route::get('board-reviews', fn () => redirect()->route('technical-review-approval.index'))->name('board-reviews.index');

    // Document Streaming & Downloading (Policy-protected)
    Route::get('/documents/{document}/stream', [DocumentStreamController::class, 'stream'])->name('documents.stream');
    Route::get('/documents/{document}/download', [DocumentStreamController::class, 'download'])->name('documents.download');

    // Admin Routes
    Route::prefix('admin')->name('admin.')->middleware('role:admin')->group(function () {
        Route::resource('areas', AreaController::class);
        Route::post('areas/{area}/parameters', [ParameterController::class, 'store'])->name('parameters.store');
        Route::put('parameters/{parameter}', [ParameterController::class, 'update'])->name('parameters.update');
        Route::delete('parameters/{parameter}', [ParameterController::class, 'destroy'])->name('parameters.destroy');

        Route::resource('users', UserController::class);
        Route::post('users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle_status');

        Route::get('assignments', [AssignmentController::class, 'index'])->name('assignments.index');
        Route::post('assignments', [AssignmentController::class, 'store'])->name('assignments.store');
        Route::delete('assignments/area/{area}/user/{user}/role/{role}', [AssignmentController::class, 'destroy'])->name('assignments.destroy');

        Route::get('audit-logs', [AuditLogController::class, 'index'])->name('audit_logs.index');
    });

    // Faculty & Document Upload Routes
    Route::post('parameter-categories/{parameterCategory}/subfolders', [SubfolderController::class, 'store'])->name('subfolders.store');
    Route::post('parameter-categories/{parameterCategory}/subfolders/batch', [SubfolderController::class, 'storeBatch'])->name('subfolders.store_batch');
    Route::put('subfolders/{subfolder}', [SubfolderController::class, 'update'])->name('subfolders.update');
    Route::put('subfolders/{subfolder}/evidence-status', [EvidenceStatusController::class, 'update'])->name('subfolders.evidence_status.update');
    Route::delete('subfolders/{subfolder}', [SubfolderController::class, 'destroy'])->name('subfolders.destroy');
    Route::post('subfolders/{subfolder}/checklist', [SubfolderController::class, 'updateChecklist'])->name('subfolders.checklist.update');
    Route::post('subfolders/{subfolder}/review-status', [SubfolderController::class, 'updateReviewStatus'])->name('subfolders.review_status.update');

    Route::post('subfolders/{subfolder}/documents', [DocumentController::class, 'store'])->name('documents.store');
    Route::post('documents/{document}/compress', [DocumentController::class, 'compress'])->name('documents.compress');
    Route::put('documents/{document}/evidences', [DocumentController::class, 'updateEvidences'])->name('documents.evidences.update');
    Route::delete('documents/{document}', [DocumentController::class, 'destroy'])->name('documents.destroy');

    // Photo Evidence Capture Routes (camera capture / gallery upload, tagged to a specific checklist item)
    Route::post('subfolders/{subfolder}/evidence-photos', [EvidencePhotoController::class, 'store'])->name('evidence_photos.store');
    Route::delete('evidence-photos/{evidencePhoto}', [EvidencePhotoController::class, 'destroy'])->name('evidence_photos.destroy');
    Route::get('evidence-photos/{evidencePhoto}/pdf', [EvidencePhotoStreamController::class, 'pdf'])->name('evidence_photos.pdf');
    Route::get('evidence-photos/{evidencePhoto}/pdf/download', [EvidencePhotoStreamController::class, 'downloadPdf'])->name('evidence_photos.pdf.download');

    // Assigned Faculty may manage Parameters only within their own Areas.
    Route::post('areas/{area}/parameters', [ParameterController::class, 'store'])->name('parameters.store');
    Route::put('parameters/{parameter}', [ParameterController::class, 'update'])->name('parameters.update');
    Route::delete('parameters/{parameter}', [ParameterController::class, 'destroy'])->name('parameters.destroy');

    // Area Report & Submission Approval Routes
    Route::get('areas/{area}/report', [\App\Http\Controllers\Admin\ReportController::class, 'showAreaReport'])->name('admin.areas.report');
    Route::post('areas/{area}/toggle-submission', [\App\Http\Controllers\Admin\ReportController::class, 'toggleAreaSubmission'])->name('admin.areas.toggle_submission');

    // Accreditor / Folder Explorer Routes
    Route::get('accreditation/browse', [BrowseController::class, 'index'])->name('accreditor.browse');
    Route::get('accreditation/areas/{area}', [BrowseController::class, 'showArea'])->name('accreditor.show_area');
    Route::post('documents/{document}/remarks', [BrowseController::class, 'storeRemark'])->name('documents.remarks.store');
    Route::put('documents/{document}/remarks/{remark}', [BrowseController::class, 'updateRemark'])->name('documents.remarks.update');
    Route::get('accreditation/areas/{area}/evaluation-report', [EvaluationController::class, 'show'])->name('accreditor.evaluation_report');
    Route::put('subfolders/{subfolder}/evaluation', [EvaluationController::class, 'store'])->name('accreditor.evaluations.store');
    Route::post('subfolders/{subfolder}/additional-document-requests', [AdditionalDocumentRequestController::class, 'store'])->name('accreditor.additional_document_requests.store');
    Route::put('documents/{document}/supplemental-evidence-review', [SupplementalEvidenceReviewController::class, 'store'])->name('accreditor.supplemental_evidence_reviews.store');
});
