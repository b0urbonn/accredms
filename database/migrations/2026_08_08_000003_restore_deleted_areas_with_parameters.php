<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('areas')
            ->whereNotNull('deleted_at')
            ->whereExists(function ($query) {
                $query->selectRaw('1')
                    ->from('parameters')
                    ->whereColumn('parameters.area_id', 'areas.id');
            })
            ->update(['deleted_at' => null]);
    }

    public function down(): void
    {
        // Restored areas are retained to protect their parameter and document data.
    }
};