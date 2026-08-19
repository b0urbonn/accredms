<?php

namespace App\Notifications;

use App\Models\AdditionalDocumentRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class EvidenceResubmitted extends Notification
{
    use Queueable;

    public function __construct(private AdditionalDocumentRequest $documentRequest, private int $areaId)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => 'Requested evidence resubmitted',
            'message' => "New evidence was uploaded for {$this->documentRequest->subfolder->code} - {$this->documentRequest->subfolder->name}.",
            'area_id' => $this->areaId,
            'subfolder_id' => $this->documentRequest->subfolder_id,
            'request_id' => $this->documentRequest->id,
        ];
    }
}