<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('areas')
            ->whereNotNull('deleted_at')
            ->whereNotExists(function ($query) {
                $query->selectRaw('1')
                    ->from('parameters')
                    ->whereColumn('parameters.area_id', 'areas.id');
            })
            ->delete();
    }

    public function down(): void
    {
        // Permanently deleted empty areas cannot be restored automatically.
    }
};