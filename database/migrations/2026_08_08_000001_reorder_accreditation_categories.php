<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('categories')->where('slug', 'implementation')->update(['sort_order' => 2]);
        DB::table('categories')->where('slug', 'outcomes')->update(['sort_order' => 3]);
    }

    public function down(): void
    {
        DB::table('categories')->where('slug', 'outcomes')->update(['sort_order' => 2]);
        DB::table('categories')->where('slug', 'implementation')->update(['sort_order' => 3]);
    }
};