<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\ProgramPerformanceComplianceFile;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProgramPerformanceComplianceController extends Controller
{
    private const AREA_CODES = ['AREA-I', 'AREA-II', 'AREA-III', 'AREA-IV', 'AREA-V', 'AREA-VI', 'AREA-VII', 'AREA-VIII', 'AREA-IX', 'AREA-X'];

    public function index(Request $request)
    {
        $this->authorize('viewAny', ProgramPerformanceComplianceFile::class);
        $user = $request->user();
        $areas = Area::query()->whereIn('code', self::AREA_CODES)->with(['programPerformanceComplianceFile.uploader'])->get()->keyBy('code');

        $rows = collect(self::AREA_CODES)->map(function (string $code, int $index) use ($areas, $user) {
            $area = $areas->get($code);
            $canView = $area && ($user->isAdmin() || $user->areas()->whereKey($area->id)->exists());

            return (object) [
                'number' => $index + 1,
                'area' => $area,
                'file' => $area?->programPerformanceComplianceFile,
                'canView' => $canView,
                'canManage' => $area && ($user->isAdmin() || ($user->isFaculty() && $user->areas()->whereKey($area->id)->wherePivotIn('assignment_role', ['handler', 'co-handler', 'member'])->exists())),
            ];
        });

        return view('program-performance-compliance.index', compact('rows'));
    }

    public function store(Request $request, Area $area)
    {
        $this->ensurePppArea($area);
        $this->authorize('upload', [ProgramPerformanceComplianceFile::class, $area]);
        abort_if($area->programPerformanceComplianceFile()->exists(), 422, 'A PPP file already exists for this Area. Use Replace File instead.');

        $file = $this->validatedPdf($request);
        $record = $this->storeFile($file, $area);
        AuditLogService::log('upload_ppp_file', $record, "Uploaded PPP file for {$area->code}");

        return back()->with('success', "PPP file uploaded for {$area->code}.");
    }

    public function update(Request $request, ProgramPerformanceComplianceFile $programPerformanceComplianceFile)
    {
        $this->authorize('update', $programPerformanceComplianceFile);
        $file = $this->validatedPdf($request);
        $oldPath = $programPerformanceComplianceFile->file_path;
        $oldDisk = $programPerformanceComplianceFile->disk;
        $this->storeFile($file, $programPerformanceComplianceFile->area, $programPerformanceComplianceFile);
        Storage::disk($oldDisk)->delete($oldPath);

        AuditLogService::log('replace_ppp_file', $programPerformanceComplianceFile, "Replaced PPP file for {$programPerformanceComplianceFile->area->code}");
        return back()->with('success', 'PPP file replaced successfully.');
    }

    public function stream(ProgramPerformanceComplianceFile $programPerformanceComplianceFile)
    {
        $this->authorize('view', $programPerformanceComplianceFile);
        $disk = Storage::disk($programPerformanceComplianceFile->disk);
        abort_unless($disk->exists($programPerformanceComplianceFile->file_path), 404, 'PPP file not found.');

        return response()->file($disk->path($programPerformanceComplianceFile->file_path), ['Content-Type' => $programPerformanceComplianceFile->mime_type]);
    }

    public function download(Request $request, ProgramPerformanceComplianceFile $programPerformanceComplianceFile)
    {
        $this->authorize('view', $programPerformanceComplianceFile);
        abort_if($request->user()->isAccreditor() && !config('accredms.accreditor_download_allowed', false), 403, 'Downloading files is restricted for Accreditor accounts.');
        $disk = Storage::disk($programPerformanceComplianceFile->disk);
        abort_unless($disk->exists($programPerformanceComplianceFile->file_path), 404, 'PPP file not found.');

        return $disk->download($programPerformanceComplianceFile->file_path, $programPerformanceComplianceFile->original_filename);
    }

    public function destroy(ProgramPerformanceComplianceFile $programPerformanceComplianceFile)
    {
        $this->authorize('delete', $programPerformanceComplianceFile);
        Storage::disk($programPerformanceComplianceFile->disk)->delete($programPerformanceComplianceFile->file_path);
        AuditLogService::log('delete_ppp_file', $programPerformanceComplianceFile, "Deleted PPP file for {$programPerformanceComplianceFile->area->code}");
        $programPerformanceComplianceFile->delete();

        return back()->with('success', 'PPP file removed successfully.');
    }

    private function validatedPdf(Request $request)
    {
        $request->validate(['file' => ['required', 'file', 'mimes:pdf', 'max:25600']]);
        $file = $request->file('file');
        $handle = fopen($file->getRealPath(), 'rb');
        $signature = fread($handle, 5);
        fclose($handle);
        abort_unless($signature === '%PDF-', 422, 'The uploaded file must be a valid PDF document.');

        return $file;
    }

    private function storeFile($file, Area $area, ?ProgramPerformanceComplianceFile $record = null): ProgramPerformanceComplianceFile
    {
        $storedFilename = (string) Str::uuid() . '.pdf';
        $path = "program-performance-compliance/{$area->id}/{$storedFilename}";
        Storage::disk('local_private')->put($path, fopen($file->getRealPath(), 'rb'));

        return ProgramPerformanceComplianceFile::updateOrCreate(['area_id' => $area->id], [
            'stored_filename' => $storedFilename,
            'original_filename' => $file->getClientOriginalName(),
            'disk' => 'local_private',
            'file_path' => $path,
            'mime_type' => 'application/pdf',
            'file_size_bytes' => $file->getSize(),
            'checksum_sha256' => hash_file('sha256', $file->getRealPath()),
            'uploaded_by' => request()->user()->id,
        ]);
    }

    private function ensurePppArea(Area $area): void
    {
        abort_unless(in_array($area->code, self::AREA_CODES, true), 404, 'Program Performance Compliance applies only to Areas I through X.');
    }
}