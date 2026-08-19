<?php

namespace App\Jobs;

use App\Models\Document;
use App\Services\PdfCompressionService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CompressPdfJob implements ShouldQueue
{
    use Queueable, InteractsWithQueue, SerializesModels;

    public function __construct(public Document $document)
    {
    }

    public function handle(PdfCompressionService $compressionService): void
    {
        $compressionService->compress($this->document);
    }
}
