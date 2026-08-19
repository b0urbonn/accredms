<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CopcFile extends Model
{
    protected $fillable = [
        'singleton_key',
        'stored_filename',
        'original_filename',
        'disk',
        'file_path',
        'mime_type',
        'file_size_bytes',
        'checksum_sha256',
        'uploaded_by',
    ];

    protected $casts = [
        'file_size_bytes' => 'integer',
    ];

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
}