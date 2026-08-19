<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Area;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use Illuminate\Support\Facades\Storage;

class DocumentStreamController extends Controller
{
    /**
     * Stream PDF document for inline preview inside PDF.js viewer modal.
     */
    public function stream(Request $request, Document $document)
    {
        $user = Auth::user();

        // Security authorization check
        $areaId = $document->subfolder->parameterCategory->parameter->area_id;
        if (Area::whereKey($areaId)->where('status', 'inactive')->exists()) {
            abort(403, 'This Area is inactive and its documents cannot be accessed.');
        }

        if (!$user->isAdmin() && !$user->areas()->where('areas.id', $areaId)->exists()) {
            abort(403, 'Unauthorized file streaming access.');
        }

        $disk = Storage::disk($document->disk);
        if (!$disk->exists($document->file_path)) {
            abort(404, 'File not found on storage server.');
        }

        AuditLogService::log('view', $document, "Streamed PDF document '{$document->original_filename}'");

        $fullPath = $disk->path($document->file_path);

        return response()->file($fullPath, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . addslashes($document->original_filename) . '"',
            'Cache-Control' => 'no-cache, private',
        ]);
    }

    /**
     * Download PDF file attachment (policy checked).
     */
    public function download(Request $request, Document $document)
    {
        $user = Auth::user();

        $areaId = $document->subfolder->parameterCategory->parameter->area_id;
        if (Area::whereKey($areaId)->where('status', 'inactive')->exists()) {
            abort(403, 'This Area is inactive and its documents cannot be accessed.');
        }

        if (!$user->isAdmin() && !$user->areas()->where('areas.id', $areaId)->exists()) {
            abort(403, 'Unauthorized file download.');
        }

        if ($user->isAccreditor() && !config('accredms.accreditor_download_allowed', false)) {
            abort(403, 'Downloading documents is restricted for Accreditor accounts.');
        }

        $disk = Storage::disk($document->disk);
        if (!$disk->exists($document->file_path)) {
            abort(404, 'File not found on storage server.');
        }

        AuditLogService::log('download', $document, "Downloaded PDF document '{$document->original_filename}'");

        return $disk->download($document->file_path, $document->original_filename);
    }
}
