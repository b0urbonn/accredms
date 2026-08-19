<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'System Input and Process', 'slug' => 'system-input-and-process', 'sort_order' => 1],
            ['name' => 'Implementation', 'slug' => 'implementation', 'sort_order' => 2],
            ['name' => 'Outcomes', 'slug' => 'outcomes', 'sort_order' => 3],
        ];

        foreach ($categories as $cat) {
            Category::firstOrCreate(['slug' => $cat['slug']], $cat);
        }
    }
}
