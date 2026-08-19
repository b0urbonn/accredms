<?php

namespace App\Services;

use App\Models\EvidencePhoto;
use App\Models\Subfolder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class EvidencePhotoUploadService
{
    public const MAX_FILE_SIZE = 15 * 1024 * 1024;
    private const MAX_DIMENSION = 2000;
    private const JPEG_QUALITY = 78;

    /**
     * Store a captured/selected photo as evidence tagged to a specific checklist item.
     */
    public function upload(UploadedFile $file, Subfolder $subfolder, ?string $checklistItem, ?string $caption = null): EvidencePhoto
    {
        if ($file->getSize() > self::MAX_FILE_SIZE) {
            throw new \InvalidArgumentException('Each photo must be 15 MB or less.');
        }

        $normalizedPath = $this->normalizeImage($file);
        $fileSize = filesize($normalizedPath);
        $checksum = hash_file('sha256', $normalizedPath);

        $paramCat = $subfolder->parameterCategory;
        $parameter = $paramCat->parameter;
        $areaId = $parameter->area_id;

        $storedFilename = (string) Str::uuid() . '.jpg';
        $directory = "evidence-photos/{$areaId}/{$parameter->id}/{$paramCat->category_id}/{$subfolder->id}";
        $filePath = "{$directory}/{$storedFilename}";

        Storage::disk('local_private')->put($filePath, fopen($normalizedPath, 'rb'));
        @unlink($normalizedPath);

        $photo = EvidencePhoto::create([
            'subfolder_id' => $subfolder->id,
            'uploaded_by' => Auth::id(),
            'checklist_item' => $checklistItem,
            'original_filename' => $file->getClientOriginalName(),
            'stored_filename' => $storedFilename,
            'disk' => 'local_private',
            'file_path' => $filePath,
            'mime_type' => 'image/jpeg',
            'file_size_bytes' => $fileSize,
            'checksum_sha256' => $checksum,
            'caption' => $caption,
            'status' => 'active',
        ]);

        AuditLogService::log('upload_evidence_photo', $photo, "Uploaded photo evidence '{$photo->original_filename}' for checklist item '{$checklistItem}'");

        return $photo;
    }

    private function normalizeImage(UploadedFile $file): string
    {
        $normalizedPath = tempnam(sys_get_temp_dir(), 'evidence-photo-');

        if (class_exists(\Imagick::class)) {
            $image = new \Imagick();
            $image->readImage($file->getRealPath());
            $image->setIteratorIndex(0);
            $image->autoOrient();
            $image->thumbnailImage(self::MAX_DIMENSION, self::MAX_DIMENSION, true);
            $image->setImageFormat('jpeg');
            $image->setImageCompression(\Imagick::COMPRESSION_JPEG);
            $image->setImageCompressionQuality(self::JPEG_QUALITY);
            $image->stripImage();
            $image->writeImage($normalizedPath);
            $image->clear();
            $image->destroy();

            return $normalizedPath;
        }

        if (function_exists('imagecreatefromstring')) {
            $source = imagecreatefromstring(file_get_contents($file->getRealPath()));
            if ($source !== false) {
                $sourceWidth = imagesx($source);
                $sourceHeight = imagesy($source);
                $scale = min(1, self::MAX_DIMENSION / max($sourceWidth, $sourceHeight));
                $targetWidth = max(1, (int) round($sourceWidth * $scale));
                $targetHeight = max(1, (int) round($sourceHeight * $scale));
                $target = imagecreatetruecolor($targetWidth, $targetHeight);
                $white = imagecolorallocate($target, 255, 255, 255);
                imagefill($target, 0, 0, $white);
                imagecopyresampled($target, $source, 0, 0, 0, 0, $targetWidth, $targetHeight, $sourceWidth, $sourceHeight);
                imagejpeg($target, $normalizedPath, self::JPEG_QUALITY);
                imagedestroy($source);
                imagedestroy($target);

                return $normalizedPath;
            }
        }

        @unlink($normalizedPath);
        throw new \InvalidArgumentException('This image could not be resized on the server.');
    }
}
