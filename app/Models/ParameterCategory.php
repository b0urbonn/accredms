<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ParameterCategory extends Model
{
    use HasFactory;

    protected $table = 'parameter_categories';

    protected $fillable = [
        'parameter_id',
        'category_id',
    ];

    public function parameter()
    {
        return $this->belongsTo(Parameter::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function subfolders()
    {
        return $this->hasMany(Subfolder::class)->whereNull('parent_id')->orderBy('code', 'asc');
    }

    public function allSubfolders()
    {
        return $this->hasMany(Subfolder::class);
    }
}
