<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TechnicalReviewApproval extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'area_id',
        'category',
        'stored_filename',
        'original_filename',
        'file_path',
        'disk',
        'mime_type',
        'file_size_bytes',
        'checksum_sha256',
        'uploaded_by',
    ];

    protected $casts = [
        'file_size_bytes' => 'integer',
    ];

    public function area()
    {
        return $this->belongsTo(Area::class);
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function getFormattedSizeAttribute(): string
    {
        if ($this->file_size_bytes >= 1048576) {
            return number_format($this->file_size_bytes / 1048576, 2) . ' MB';
        }

        return number_format($this->file_size_bytes / 1024, 2) . ' KB';
    }

    public function getCategoryBadgeAttribute(): string
    {
        return match ($this->category) {
            'technical_review' => 'bg-info text-dark',
            'board_approval' => 'bg-primary text-white',
            default => 'bg-success text-white',
        };
    }

    public function getCategoryLabelAttribute(): string
    {
        return match ($this->category) {
            'technical_review' => 'Technical Review',
            'board_approval' => 'Board Approval',
            default => 'Technical Review & Approval',
        };
    }
}
