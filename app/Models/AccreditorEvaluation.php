<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccreditorEvaluation extends Model
{
    use HasFactory;

    protected $fillable = [
        'subfolder_id',
        'user_id',
        'rating',
        'compliance_result',
        'evaluation',
    ];

    protected function casts(): array
    {
        return [
            'rating' => 'decimal:1',
        ];
    }

    public function subfolder()
    {
        return $this->belongsTo(Subfolder::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}