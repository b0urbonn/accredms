<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('subfolders')
            ->where('review_status', 'no_evidence')
            ->whereExists(function ($query) {
                $query->selectRaw('1')
                    ->from('documents')
                    ->whereColumn('documents.subfolder_id', 'subfolders.id')
                    ->whereNull('documents.deleted_at');
            })
            ->update(['review_status' => 'under_review']);
    }

    public function down(): void
    {
    }
};