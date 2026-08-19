<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ComplianceEvidence extends Model
{
    protected $table = 'compliance_evidences';

    protected $fillable = [
        'compliance_recommendation_id',
        'uploaded_by',
        'original_filename',
        'stored_filename',
        'disk',
        'file_path',
        'mime_type',
        'file_size_bytes',
        'checksum_sha256',
    ];

    protected $casts = [
        'file_size_bytes' => 'integer',
    ];

    public function recommendation()
    {
        return $this->belongsTo(ComplianceRecommendation::class, 'compliance_recommendation_id');
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

        if ($this->file_size_bytes >= 1024) {
            return number_format($this->file_size_bytes / 1024, 2) . ' KB';
        }

        return $this->file_size_bytes . ' B';
    }
}