<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Parameter extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'area_id',
        'code',
        'title',
        'description',
        'sort_order',
        'status',
    ];

    public function area()
    {
        return $this->belongsTo(Area::class);
    }

    public function parameterCategories()
    {
        return $this->hasMany(ParameterCategory::class)
            ->orderBy(
                Category::select('sort_order')
                    ->whereColumn('categories.id', 'parameter_categories.category_id')
            );
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'parameter_categories')
            ->withTimestamps();
    }

    public function getProgressAttribute(): array
    {
        $paramCatIds = $this->parameterCategories->pluck('id');
        if ($paramCatIds->isEmpty()) {
            return [
                'total' => 0,
                'completed' => 0,
                'missing' => 0,
                'percent' => 0,
            ];
        }

        $subfolders = Subfolder::query()
            ->where('status', 'active')
            ->whereIn('parameter_category_id', $paramCatIds)
            ->withCount(['documents', 'photos'])
            ->get();

        $total = $subfolders->count();
        $completed = $subfolders->filter(fn ($s) => $s->documents_count > 0 || $s->photos_count > 0)->count();
        $missing = $total - $completed;
        $percent = $total > 0 ? (int) round(($completed / $total) * 100) : 0;

        return [
            'total' => $total,
            'completed' => $completed,
            'missing' => $missing,
            'percent' => $percent,
        ];
    }
}
