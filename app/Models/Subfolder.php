<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Subfolder extends Model
{
    use HasFactory, SoftDeletes;

    public const EVIDENCE_STATUSES = ['draft', 'submitted', 'under_review', 'needs_revision', 'approved'];
    public const REVIEW_STATUSES = ['no_evidence', 'under_review', 'additional_documents_requested', 'resubmitted', 'evaluated'];

    protected $fillable = [
        'parameter_category_id',
        'parent_id',
        'code',
        'name',
        'documents_needed',
        'completed_checklist_items',
        'created_by',
        'status',
        'evidence_status',
        'review_status',
    ];

    protected $casts = [
        'completed_checklist_items' => 'array',
    ];

    /**
     * Get array of parsed checklist items from documents_needed.
     */
    public function getParsedChecklistAttribute(): array
    {
        if (!$this->documents_needed) {
            return [];
        }

        $lines = preg_split('/\r\n|\r|\n|•/', $this->documents_needed);
        $items = [];

        foreach ($lines as $line) {
            $trimmed = trim($line);
            $trimmed = ltrim($trimmed, "•\t- *");
            if ($trimmed !== '') {
                $items[] = $trimmed;
            }
        }

        return array_unique($items);
    }

    /**
     * Get checklist items covered by currently existing PDF or photo evidence.
     */
    public function getCompletedChecklistArrayAttribute(): array
    {
        if (!$this->documents()->exists() && !$this->photos()->exists()) {
            return [];
        }

        $allCovered = [];
        foreach ($this->documents as $doc) {
            if (is_array($doc->covered_evidences)) {
                $allCovered = array_merge($allCovered, $doc->covered_evidences);
            }
        }
        foreach ($this->photos as $photo) {
            if ($photo->checklist_item && $photo->checklist_item !== 'General Evidence') {
                $allCovered[] = $photo->checklist_item;
            }
        }

        return array_values(array_unique($allCovered));
    }

    /**
     * Get completion statistics for required checklist items.
     */
    public function getChecklistStatsAttribute(): array
    {
        $all = $this->parsed_checklist;
        $total = count($all);
        if ($total === 0) {
            return [
                'total' => 0,
                'completed' => 0,
                'missing' => 0,
                'is_complete' => false,
                'has_checklist' => false,
            ];
        }

        $completedList = $this->completed_checklist_array;
        $completedCount = 0;

        foreach ($all as $item) {
            if (in_array($item, $completedList, true)) {
                $completedCount++;
            }
        }

        return [
            'total' => $total,
            'completed' => $completedCount,
            'missing' => max(0, $total - $completedCount),
            'is_complete' => ($completedCount >= $total),
            'has_checklist' => true,
        ];
    }

    public function parameterCategory()
    {
        return $this->belongsTo(ParameterCategory::class);
    }

    public function parent()
    {
        return $this->belongsTo(Subfolder::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Subfolder::class, 'parent_id')->orderBy('code', 'asc');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function documents()
    {
        return $this->hasMany(Document::class)->orderBy('created_at', 'desc');
    }

    public function photos()
    {
        return $this->hasMany(EvidencePhoto::class)->orderBy('created_at', 'desc');
    }

    public function evaluations()
    {
        return $this->hasMany(AccreditorEvaluation::class)->latest();
    }

    public function additionalDocumentRequests()
    {
        return $this->hasMany(AdditionalDocumentRequest::class)->latest();
    }

    public function hasDocumentsInTree(): bool
    {
        if ($this->documents()->exists() || $this->photos()->exists()) {
            return true;
        }

        return $this->children()->get()->contains(
            fn (Subfolder $child) => $child->hasDocumentsInTree()
        );
    }

    public function deleteTree(): void
    {
        $this->children()->get()->each(
            fn (Subfolder $child) => $child->deleteTree()
        );

        $this->delete();
    }
}
