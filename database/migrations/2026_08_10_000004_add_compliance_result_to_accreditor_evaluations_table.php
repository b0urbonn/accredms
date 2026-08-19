<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('accreditor_evaluations', function (Blueprint $table) {
            $table->enum('compliance_result', ['complied', 'partially_complied', 'not_complied'])
                ->nullable()
                ->after('rating');
        });
    }

    public function down(): void
    {
        Schema::table('accreditor_evaluations', function (Blueprint $table) {
            $table->dropColumn('compliance_result');
        });
    }
};