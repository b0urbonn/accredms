<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\TechnicalReviewApproval;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TechnicalReviewApprovalController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', TechnicalReviewApproval::class);
        $user = $request->user();

        $query = TechnicalReviewApproval::with(['area', 'uploader']);

        if ($user->isFaculty()) {
            $userAreaIds = $user->areas()->pluck('areas.id')->toArray();
            $query->where(function ($q) use ($userAreaIds) {
                $q->whereNull('area_id')->orWhereIn('area_id', $userAreaIds);
            });
        }

        if ($request->filled('area_id')) {
            $query->where('area_id', $request->integer('area_id'));
        }

        if ($request->filled('category')) {
            $query->where('category', $request->input('category'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('original_filename', 'like', "%{$search}%")
                  ->orWhere('stored_filename', 'like', "%{$search}%");
            });
        }

        $reports = $query->latest()->paginate(15)->withQueryString();
        $areas = ($user->isAdmin() || $user->isAccreditor()) 
            ? Area::where('status', '!=', 'inactive')->get() 
            : $user->areas()->get();

        return view('technical-review-approval.index', compact('reports', 'areas'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', TechnicalReviewApproval::class);

        $request->validate([
            'area_id' => ['nullable', 'exists:areas,id'],
            'category' => ['nullable', 'string', 'in:technical_review,board_approval,general'],
            'files' => ['required_without:file', 'array'],
            'files.*' => ['file', 'mimes:pdf,doc,docx,xls,xlsx,zip', 'max:30720'],
            'file' => ['required_without:files', 'file', 'mimes:pdf,doc,docx,xls,xlsx,zip', 'max:30720'],
        ]);

        $uploadedFiles = [];
        if ($request->hasFile('files')) {
            $uploadedFiles = $request->file('files');
        } elseif ($request->hasFile('file')) {
            $uploadedFiles = [$request->file('file')];
        }

        $areaId = $request->input('area_id') ? (int)$request->input('area_id') : null;
        $category = $request->input('category', 'general');
        $user = $request->user();
        $count = 0;

        foreach ($uploadedFiles as $file) {
            if (!$file->isValid()) {
                continue;
            }

            $extension = strtolower($file->getClientOriginalExtension());
            $storedFilename = (string) Str::uuid() . '.' . $extension;
            $path = "technical-review-approval/{$storedFilename}";

            Storage::disk('local_private')->put($path, fopen($file->getRealPath(), 'rb'));

            $record = TechnicalReviewApproval::create([
                'area_id' => $areaId,
                'category' => $category,
                'stored_filename' => $storedFilename,
                'original_filename' => $file->getClientOriginalName(),
                'file_path' => $path,
                'disk' => 'local_private',
                'mime_type' => $file->getClientMimeType(),
                'file_size_bytes' => $file->getSize(),
                'checksum_sha256' => hash_file('sha256', $file->getRealPath()),
                'uploaded_by' => $user->id,
            ]);

            AuditLogService::log('upload_technical_review_approval', $record, "Uploaded report file '{$file->getClientOriginalName()}'");
            $count++;
        }

        $message = $count > 1 
            ? "Successfully uploaded {$count} report files."
            : "Report file uploaded successfully.";

        return back()->with('success', $message);
    }

    public function stream(TechnicalReviewApproval $technicalReviewApproval)
    {
        $this->authorize('view', $technicalReviewApproval);
        $disk = Storage::disk($technicalReviewApproval->disk);
        abort_unless($disk->exists($technicalReviewApproval->file_path), 404, 'Report file not found.');

        return response()->file($disk->path($technicalReviewApproval->file_path), [
            'Content-Type' => $technicalReviewApproval->mime_type ?? 'application/pdf',
        ]);
    }

    public function download(Request $request, TechnicalReviewApproval $technicalReviewApproval)
    {
        $this->authorize('view', $technicalReviewApproval);
        $disk = Storage::disk($technicalReviewApproval->disk);
        abort_unless($disk->exists($technicalReviewApproval->file_path), 404, 'Report file not found.');

        return $disk->download($technicalReviewApproval->file_path, $technicalReviewApproval->original_filename);
    }

    public function destroy(TechnicalReviewApproval $technicalReviewApproval)
    {
        $this->authorize('delete', $technicalReviewApproval);
        $filename = $technicalReviewApproval->original_filename;

        if (Storage::disk($technicalReviewApproval->disk)->exists($technicalReviewApproval->file_path)) {
            Storage::disk($technicalReviewApproval->disk)->delete($technicalReviewApproval->file_path);
        }

        $technicalReviewApproval->delete();

        AuditLogService::log('delete_technical_review_approval', $technicalReviewApproval, "Deleted report file '{$filename}'");

        return back()->with('success', "Report file '{$filename}' removed successfully.");
    }
}
