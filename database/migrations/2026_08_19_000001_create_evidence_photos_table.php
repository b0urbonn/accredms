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
        Schema::create('evidence_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subfolder_id')->constrained('subfolders')->cascadeOnDelete();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            // The specific checklist / "documents needed" line item this photo is evidence for.
            $table->string('checklist_item', 255)->nullable();
            $table->string('original_filename', 255);
            $table->string('stored_filename', 255);
            $table->string('disk', 50)->default('local_private');
            $table->string('file_path', 500);
            $table->string('mime_type', 100);
            $table->bigInteger('file_size_bytes');
            $table->string('checksum_sha256', 64);
            $table->string('caption', 255)->nullable();
            $table->enum('status', ['active', 'archived'])->default('active');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('evidence_photos');
    }
};
