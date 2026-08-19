<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('subfolders', function (Blueprint $table) {
            $table->json('completed_checklist_items')->nullable()->after('documents_needed');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subfolders', function (Blueprint $table) {
            $table->dropColumn('completed_checklist_items');
        });
    }
};
