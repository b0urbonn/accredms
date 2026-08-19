<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class TechnicalReport extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'area_id',
        'report_number',
        'title',
        'program',
        'survey_visit',
        'summary_findings',
        'technical_evaluation',
        'strengths',
        'areas_for_improvement',
        'recommendations',
        'overall_score',
        'status',
        'prepared_by',
        'updated_by',
    ];

    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'prepared_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'published' => 'text-bg-success',
            'approved' => 'text-bg-primary',
            'under_review' => 'text-bg-warning',
            default => 'text-bg-secondary',
        };
    }
}
