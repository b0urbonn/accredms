<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdditionalDocumentRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'subfolder_id',
        'requested_by',
        'assigned_to',
        'requested_documents',
        'remarks',
        'due_date',
        'status',
        'fulfilled_at',
    ];

    protected function casts(): array
    {
        return [
            'due_date' => 'date',
            'fulfilled_at' => 'datetime',
        ];
    }

    public function subfolder()
    {
        return $this->belongsTo(Subfolder::class);
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
}