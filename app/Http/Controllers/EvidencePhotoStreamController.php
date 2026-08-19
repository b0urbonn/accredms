<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\EvidencePhoto;
use App\Services\AuditLogService;
use App\Services\EvidencePhotoPdfService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EvidencePhotoStreamController extends Controller
{
    public function pdf(Request $request, EvidencePhoto $evidencePhoto, EvidencePhotoPdfService $pdfService)
    {
        $this->authorizeAccess($evidencePhoto);
        $pdf = $pdfService->render($evidencePhoto);

        AuditLogService::log('view', $evidencePhoto, "Viewed photo evidence PDF for checklist item '{$evidencePhoto->checklist_item}'");

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $pdfService->filename($evidencePhoto) . '"',
            'Cache-Control' => 'no-cache, private',
        ]);
    }

    public function downloadPdf(Request $request, EvidencePhoto $evidencePhoto, EvidencePhotoPdfService $pdfService)
    {
        $this->authorizeAccess($evidencePhoto);
        $pdf = $pdfService->render($evidencePhoto);

        if (Auth::user()->isAccreditor() && !config('accredms.accreditor_download_allowed', false)) {
            abort(403, 'Downloading evidence is restricted for Accreditor accounts.');
        }

        AuditLogService::log('download', $evidencePhoto, "Downloaded photo evidence PDF for checklist item '{$evidencePhoto->checklist_item}'");

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $pdfService->filename($evidencePhoto) . '"',
        ]);
    }

    private function authorizeAccess(EvidencePhoto $evidencePhoto): void
    {
        $user = Auth::user();
        $areaId = $evidencePhoto->subfolder->parameterCategory->parameter->area_id;

        if (Area::whereKey($areaId)->where('status', 'inactive')->exists()) {
            abort(403, 'This Area is inactive and its evidence cannot be accessed.');
        }

        if (!$user->isAdmin() && !$user->areas()->where('areas.id', $areaId)->exists()) {
            abort(403, 'Unauthorized evidence access.');
        }
    }
}
