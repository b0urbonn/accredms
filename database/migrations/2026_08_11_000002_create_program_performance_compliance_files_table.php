<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('program_performance_compliance_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('area_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('stored_filename', 255);
            $table->string('original_filename', 255);
            $table->string('disk', 50)->default('local_private');
            $table->string('file_path', 500);
            $table->string('mime_type', 100)->default('application/pdf');
            $table->unsignedBigInteger('file_size_bytes');
            $table->string('checksum_sha256', 64);
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('program_performance_compliance_files');
    }
};