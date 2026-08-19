<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ComplianceRecommendation extends Model
{
    protected $fillable = [
        'compliance_report_id',
        'recommendation',
        'action_taken',
        'compliance_percentage',
        'sort_order',
    ];

    protected $casts = [
        'compliance_percentage' => 'integer',
    ];

    public function report()
    {
        return $this->belongsTo(ComplianceReport::class, 'compliance_report_id');
    }

    public function evidences()
    {
        return $this->hasMany(ComplianceEvidence::class)->latest();
    }
}