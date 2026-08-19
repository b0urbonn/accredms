<?php

namespace App\Services;

use App\Models\Document;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;

class PdfCompressionService
{
    public const MAX_FINAL_FILE_SIZE = 25 * 1024 * 1024;

    public function isAvailable(): bool
    {
        return $this->findGhostscriptBinary() !== null;
    }

    /**
     * Compress a temporary upload to the maximum permitted stored size.
     */
    public function compressToMaximumSize(string $sourcePath, int $maximumSize = self::MAX_FINAL_FILE_SIZE): ?string
    {
        if (!file_exists($sourcePath)) {
            return null;
        }

        if (filesize($sourcePath) <= $maximumSize) {
            return $sourcePath;
        }

        $gsBinary = $this->findGhostscriptBinary();
        if (!$gsBinary) {
            return null;
        }

        foreach (['/ebook', '/screen'] as $profile) {
            $compressedPath = $sourcePath . '.' . ltrim($profile, '/') . '.pdf';
            $result = Process::run([
                $gsBinary,
                '-sDEVICE=pdfwrite',
                '-dCompatibilityLevel=1.4',
                '-dPDFSETTINGS=' . $profile,
                '-dNOPAUSE',
                '-dQUIET',
                '-dBATCH',
                '-sOutputFile=' . $compressedPath,
                $sourcePath,
            ]);

            if ($result->successful() && file_exists($compressedPath) && filesize($compressedPath) > 0) {
                if (filesize($compressedPath) <= $maximumSize) {
                    return $compressedPath;
                }

                @unlink($compressedPath);
            }
        }

        return null;
    }

    /**
     * Compress a PDF document.
     */
    public function compress(Document $document): bool
    {
        $disk = Storage::disk($document->disk);
        $fullPath = $disk->path($document->file_path);

        if (!file_exists($fullPath)) {
            $document->update(['compression_status' => 'failed']);
            return false;
        }

        if (!$this->isAvailable()) {
            $document->update(['compression_status' => 'failed']);
            return false;
        }

        $document->update(['compression_status' => 'processing']);

        $originalSize = filesize($fullPath);
        if (!$document->original_size_bytes) {
            $document->original_size_bytes = $originalSize;
        }

        $compressedPath = $this->compressToMaximumSize($fullPath, $originalSize - 1);

        if ($compressedPath && $compressedPath !== $fullPath) {
            $compressedSize = filesize($compressedPath);
            copy($compressedPath, $fullPath);
            @unlink($compressedPath);

            $document->update([
                'file_size_bytes' => $compressedSize,
                'is_compressed' => true,
                'compression_status' => 'done',
            ]);

            AuditLogService::log('compress', $document, "Compressed PDF from {$originalSize} to {$compressedSize} bytes");
            return true;
        }

        // If compression was attempted or skipped, mark as done or none safely
        $document->update([
            'is_compressed' => false,
            'compression_status' => 'done',
        ]);

        return true;
    }

    /**
     * Find available Ghostscript binary on the system.
     */
    protected function findGhostscriptBinary(): ?string
    {
        $binaries = ['gs', 'gswin64c', 'gswin32c', 'C:\Program Files\gs\gs10.03.0\bin\gswin64c.exe'];

        foreach ($binaries as $bin) {
            $check = Process::run([$bin, '-v']);
            if ($check->successful()) {
                return $bin;
            }
        }

        return null;
    }
}
