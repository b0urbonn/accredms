<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subfolders', function (Blueprint $table) {
            $table->enum('evidence_status', ['draft', 'submitted', 'under_review', 'needs_revision', 'approved'])
                ->default('draft')
                ->after('status');
        });

        DB::table('subfolders')
            ->whereNull('evidence_status')
            ->update(['evidence_status' => 'draft']);
    }

    public function down(): void
    {
        Schema::table('subfolders', function (Blueprint $table) {
            $table->dropColumn('evidence_status');
        });
    }
};