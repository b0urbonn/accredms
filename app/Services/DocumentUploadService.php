<?php

namespace App\Services;

use App\Jobs\CompressPdfJob;
use App\Models\Document;
use App\Models\Subfolder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DocumentUploadService
{
    public function __construct(private PdfCompressionService $compressionService)
    {
    }

    /**
     * Upload and store a PDF document.
     */
    public function upload(UploadedFile $file, Subfolder $subfolder, bool $forceCompress = false, array $coveredEvidences = []): Document
    {
        // 1. Validate PDF signature / magic bytes (%PDF-)
        $handle = fopen($file->getRealPath(), 'rb');
        $header = fread($handle, 5);
        fclose($handle);

        if ($header !== '%PDF-') {
            throw new \InvalidArgumentException('Invalid file format. The file is not a valid PDF document.');
        }

        $originalSize = $file->getSize();
        $sourcePath = $file->getRealPath();
        $compressedPath = null;

        if (($forceCompress || $originalSize > PdfCompressionService::MAX_FINAL_FILE_SIZE) && !$this->compressionService->isAvailable()) {
            throw new \InvalidArgumentException('PDF compression is unavailable because Ghostscript is not installed on this server.');
        }

        // Oversized files must become compliant before anything is stored.
        if ($originalSize > PdfCompressionService::MAX_FINAL_FILE_SIZE) {
            $compressedPath = $this->compressionService->compressToMaximumSize($sourcePath);

            if (!$compressedPath) {
                throw new \InvalidArgumentException('This PDF is larger than 25 MB and could not be compressed to 25 MB or less. Please reduce its size and try again.');
            }

            $sourcePath = $compressedPath;
        }

        $fileSize = filesize($sourcePath);
        if ($fileSize > PdfCompressionService::MAX_FINAL_FILE_SIZE) {
            throw new \InvalidArgumentException('The final PDF must be 25 MB or less.');
        }

        // 2. Compute the checksum for the final stored PDF.
        $checksum = hash_file('sha256', $sourcePath);

        // 3. Prepare storage paths
        $paramCat = $subfolder->parameterCategory;
        $parameter = $paramCat->parameter;
        $areaId = $parameter->area_id;

        $storedFilename = (string) Str::uuid() . '.pdf';
        $directory = "documents/{$areaId}/{$parameter->id}/{$paramCat->category_id}/{$subfolder->id}";
        $filePath = "{$directory}/{$storedFilename}";

        // 4. Store file on local_private disk
        Storage::disk('local_private')->put($filePath, fopen($sourcePath, 'rb'));

        if ($compressedPath) {
            @unlink($compressedPath);
        }

        // 5. Create Document record
        $document = Document::create([
            'subfolder_id' => $subfolder->id,
            'uploaded_by' => Auth::id(),
            'original_filename' => $file->getClientOriginalName(),
            'stored_filename' => $storedFilename,
            'disk' => 'local_private',
            'file_path' => $filePath,
            'mime_type' => 'application/pdf',
            'file_size_bytes' => $fileSize,
            'original_size_bytes' => $originalSize,
            'is_compressed' => $fileSize < $originalSize,
            'compression_status' => $fileSize < $originalSize ? 'done' : (($fileSize > 10 * 1024 * 1024 || $forceCompress) ? 'pending' : 'none'),
            'checksum_sha256' => $checksum,
            'version' => 1,
            'status' => 'active',
            'covered_evidences' => array_values(array_unique($coveredEvidences)),
        ]);

        // 6. Record initial version in document_versions table
        $document->versions()->create([
            'version' => 1,
            'file_path' => $filePath,
            'file_size_bytes' => $fileSize,
            'uploaded_by' => Auth::id(),
            'created_at' => now(),
        ]);

        // 7. Trigger queued compression if required
        if ($fileSize === $originalSize && ($fileSize > 10 * 1024 * 1024 || $forceCompress)) {
            CompressPdfJob::dispatch($document);
        }

        // 8. Log audit trail
        AuditLogService::log('upload', $document, "Uploaded PDF file '{$document->original_filename}' ({$document->formatted_size})");

        return $document;
    }
}
