<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subfolders', function (Blueprint $table) {
            $table->enum('review_status', ['no_evidence', 'under_review', 'additional_documents_requested', 'resubmitted', 'evaluated'])
                ->default('no_evidence')
                ->after('evidence_status');
        });
    }

    public function down(): void
    {
        Schema::table('subfolders', function (Blueprint $table) {
            $table->dropColumn('review_status');
        });
    }
};