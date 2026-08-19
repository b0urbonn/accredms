<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupplementalEvidenceReview extends Model
{
    use HasFactory;

    protected $fillable = ['document_id', 'user_id', 'result', 'comment'];

    public function document()
    {
        return $this->belongsTo(Document::class);
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}