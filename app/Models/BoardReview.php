<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class BoardReview extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'area_id',
        'resolution_number',
        'review_title',
        'program',
        'survey_visit',
        'board_decision',
        'validity_period',
        'board_remarks',
        'conditions_set',
        'reviewed_date',
        'status',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'reviewed_date' => 'date',
    ];

    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function getDecisionBadgeAttribute(): string
    {
        return match ($this->board_decision) {
            'accredited_level_1', 'accredited_level_2', 'accredited_level_3', 'accredited_level_4', 're_accredited' => 'text-bg-success',
            'under_board_review' => 'text-bg-info',
            'deferred' => 'text-bg-warning',
            'not_accredited' => 'text-bg-danger',
            default => 'text-bg-secondary',
        };
    }

    public function getFormattedDecisionAttribute(): string
    {
        return ucwords(str_replace('_', ' ', $this->board_decision));
    }
}
