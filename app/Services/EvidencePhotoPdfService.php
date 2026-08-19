<?php

namespace App\Services;

use App\Models\EvidencePhoto;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class EvidencePhotoPdfService
{
    public function render(EvidencePhoto $evidencePhoto): string
    {
        $photos = $this->photosForStatement($evidencePhoto);
        $subfolder = $evidencePhoto->subfolder;
        $subfolder->loadMissing([
            'parameterCategory.parameter.area',
            'parameterCategory.category',
        ]);

        $photoGroups = [];
        foreach ($subfolder->parsed_checklist as $checklistItem) {
            $photoGroups[$checklistItem] = [];
        }

        foreach ($photos as $photo) {
            $disk = Storage::disk($photo->disk);
            if (!$disk->exists($photo->file_path)) {
                continue;
            }

            $imageData = $this->imageData($disk->get($photo->file_path), strtolower($photo->mime_type));
            if (!$imageData) {
                continue;
            }

            $checklistItem = $photo->checklist_item ?: 'General Evidence';
            $photoGroups[$checklistItem] ??= [];
            $photoGroups[$checklistItem][] = $imageData;
        }

        $options = new Options();
        $options->set('isRemoteEnabled', false);
        $options->set('defaultFont', 'DejaVu Sans');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml(view('pdf.evidence_photos', [
            'area' => $subfolder->parameterCategory->parameter->area,
            'parameter' => $subfolder->parameterCategory->parameter,
            'category' => $subfolder->parameterCategory->category,
            'subfolder' => $subfolder,
            'photoGroups' => $photoGroups,
        ])->render());
        $dompdf->setPaper('a4', 'portrait');
        $dompdf->render();

        return $dompdf->output();
    }

    public function photosForStatement(EvidencePhoto $evidencePhoto): Collection
    {
        return EvidencePhoto::query()
            ->where('subfolder_id', $evidencePhoto->subfolder_id)
            ->orderBy('created_at')
            ->get();
    }

    public function filename(EvidencePhoto $evidencePhoto): string
    {
        $subfolder = $evidencePhoto->subfolder;
        return Str::slug($subfolder->code ?: 'statement') . '-photo-evidence.pdf';
    }

    private function imageData(string $contents, string $mimeType): ?array
    {
        if (in_array($mimeType, ['image/jpeg', 'image/png'], true)) {
            $dimensions = getimagesizefromstring($contents);
            if (!$dimensions) {
                return null;
            }

            return [
                'data' => 'data:' . $mimeType . ';base64,' . base64_encode($contents),
                'width' => $dimensions[0],
                'height' => $dimensions[1],
            ];
        }

        if (!class_exists(\Imagick::class)) {
            return null;
        }

        try {
            $image = new \Imagick();
            $image->readImageBlob($contents);
            $image->setIteratorIndex(0);
            $image->setImageFormat('jpeg');
            $image->setImageCompressionQuality(88);
            $image->stripImage();
            $jpeg = $image->getImageBlob();
            $width = $image->getImageWidth();
            $height = $image->getImageHeight();
            $image->clear();
            $image->destroy();

            return [
                'data' => 'data:image/jpeg;base64,' . base64_encode($jpeg),
                'width' => $width,
                'height' => $height,
            ];
        } catch (\Throwable) {
            return null;
        }
    }
}
