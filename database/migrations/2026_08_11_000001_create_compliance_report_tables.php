<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('compliance_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('area_id')->constrained()->cascadeOnDelete();
            $table->string('program', 255)->nullable();
            $table->string('survey_visit', 255)->nullable();
            $table->string('status', 30)->default('uploaded');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('compliance_recommendations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('compliance_report_id')->constrained()->cascadeOnDelete();
            $table->text('recommendation');
            $table->text('action_taken')->nullable();
            $table->unsignedTinyInteger('compliance_percentage')->default(0);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('compliance_evidences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('compliance_recommendation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('original_filename', 255);
            $table->string('stored_filename', 255);
            $table->string('disk', 50)->default('local_private');
            $table->string('file_path', 500);
            $table->string('mime_type', 100);
            $table->unsignedBigInteger('file_size_bytes');
            $table->string('checksum_sha256', 64);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('compliance_evidences');
        Schema::dropIfExists('compliance_recommendations');
        Schema::dropIfExists('compliance_reports');
    }
};