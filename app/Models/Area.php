<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Area extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'description',
        'status',
        'created_by',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function parameters()
    {
        return $this->hasMany(Parameter::class)->orderBy('sort_order', 'asc')->orderBy('code', 'asc');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'area_user')
            ->withPivot('assignment_role', 'assigned_by', 'assigned_at')
            ->withTimestamps();
    }

    public function handlers()
    {
        return $this->users()->wherePivot('assignment_role', 'handler');
    }

    public function coHandlers()
    {
        return $this->users()->wherePivot('assignment_role', 'co-handler');
    }

    public function members()
    {
        return $this->users()->wherePivot('assignment_role', 'member');
    }

    public function accreditors()
    {
        return $this->users()->wherePivot('assignment_role', 'accreditor');
    }

    public function complianceReports()
    {
        return $this->hasMany(ComplianceReport::class);
    }

    public function programPerformanceComplianceFile()
    {
        return $this->hasOne(ProgramPerformanceComplianceFile::class);
    }
}
