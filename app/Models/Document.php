<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Document extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'subfolder_id',
        'uploaded_by',
        'original_filename',
        'stored_filename',
        'disk',
        'file_path',
        'mime_type',
        'file_size_bytes',
        'original_size_bytes',
        'is_compressed',
        'compression_status',
        'checksum_sha256',
        'version',
        'status',
        'covered_evidences',
    ];

    protected $casts = [
        'is_compressed' => 'boolean',
        'file_size_bytes' => 'integer',
        'original_size_bytes' => 'integer',
        'version' => 'integer',
        'covered_evidences' => 'array',
    ];

    public function getCoveredEvidencesArrayAttribute(): array
    {
        return is_array($this->covered_evidences) ? $this->covered_evidences : [];
    }

    public function subfolder()
    {
        return $this->belongsTo(Subfolder::class);
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function versions()
    {
        return $this->hasMany(DocumentVersion::class)->orderBy('version', 'desc');
    }

    public function remarks()
    {
        return $this->hasMany(DocumentRemark::class)->orderBy('created_at', 'desc');
    }

    public function supplementalEvidenceReviews()
    {
        return $this->hasMany(SupplementalEvidenceReview::class)->latest();
    }

    public function getFormattedSizeAttribute()
    {
        $bytes = $this->file_size_bytes;
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        }
        return $bytes . ' B';
    }
}
