<?php

namespace App\Http\Controllers;

use App\Models\CopcFile;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CopcController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', CopcFile::class);

        return view('copc.index', ['copcFile' => CopcFile::with('uploader')->first()]);
    }

    public function store(Request $request)
    {
        $this->authorize('create', CopcFile::class);
        $file = $this->validatedPdf($request);
        $existing = CopcFile::first();
        $oldDisk = $existing?->disk;
        $oldPath = $existing?->file_path;
        $copcFile = $this->storeFile($file);

        if ($oldDisk && $oldPath) {
            Storage::disk($oldDisk)->delete($oldPath);
        }

        AuditLogService::log($existing ? 'replace_copc_file' : 'upload_copc_file', $copcFile, $existing ? 'Replaced COPC PDF' : 'Uploaded COPC PDF');
        return back()->with('success', $existing ? 'COPC PDF replaced successfully.' : 'COPC PDF uploaded successfully.');
    }

    public function stream(CopcFile $copcFile)
    {
        $this->authorize('view', $copcFile);
        $disk = Storage::disk($copcFile->disk);
        abort_unless($disk->exists($copcFile->file_path), 404, 'COPC file not found.');

        return response()->file($disk->path($copcFile->file_path), ['Content-Type' => $copcFile->mime_type]);
    }

    public function download(Request $request, CopcFile $copcFile)
    {
        $this->authorize('view', $copcFile);
        abort_if($request->user()->isAccreditor() && !config('accredms.accreditor_download_allowed', false), 403, 'Downloading COPC files is restricted for Accreditor accounts.');
        $disk = Storage::disk($copcFile->disk);
        abort_unless($disk->exists($copcFile->file_path), 404, 'COPC file not found.');

        return $disk->download($copcFile->file_path, $copcFile->original_filename);
    }

    public function destroy(CopcFile $copcFile)
    {
        $this->authorize('delete', $copcFile);
        Storage::disk($copcFile->disk)->delete($copcFile->file_path);
        AuditLogService::log('delete_copc_file', $copcFile, 'Deleted COPC PDF');
        $copcFile->delete();

        return back()->with('success', 'COPC PDF removed successfully.');
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

    private function storeFile($file): CopcFile
    {
        $storedFilename = (string) Str::uuid() . '.pdf';
        $path = "copc/{$storedFilename}";
        Storage::disk('local_private')->put($path, fopen($file->getRealPath(), 'rb'));

        return CopcFile::updateOrCreate(['singleton_key' => 'current'], [
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
}