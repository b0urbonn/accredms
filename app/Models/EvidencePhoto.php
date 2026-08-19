<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EvidencePhoto extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'subfolder_id',
        'uploaded_by',
        'checklist_item',
        'original_filename',
        'stored_filename',
        'disk',
        'file_path',
        'mime_type',
        'file_size_bytes',
        'checksum_sha256',
        'caption',
        'status',
    ];

    protected $casts = [
        'file_size_bytes' => 'integer',
    ];

    public function subfolder()
    {
        return $this->belongsTo(Subfolder::class);
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
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
