<?php

namespace App\Observers;

use App\Models\Category;
use App\Models\Parameter;
use App\Models\ParameterCategory;

class ParameterObserver
{
    /**
     * Handle the Parameter "created" event.
     */
    public function created(Parameter $parameter): void
    {
        $categories = Category::orderBy('sort_order', 'asc')->get();

        // Ensure default categories exist if not already seeded
        if ($categories->isEmpty()) {
            $defaultCategories = [
                ['name' => 'System Input and Process', 'slug' => 'system-input-and-process', 'sort_order' => 1],
                ['name' => 'Implementation', 'slug' => 'implementation', 'sort_order' => 2],
                ['name' => 'Outcomes', 'slug' => 'outcomes', 'sort_order' => 3],
            ];

            foreach ($defaultCategories as $catData) {
                Category::firstOrCreate(['slug' => $catData['slug']], $catData);
            }

            $categories = Category::orderBy('sort_order', 'asc')->get();
        }

        foreach ($categories as $category) {
            ParameterCategory::firstOrCreate([
                'parameter_id' => $parameter->id,
                'category_id' => $category->id,
            ]);
        }
    }
}
