<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('technical_review_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('area_id')->nullable()->constrained('areas')->nullOnDelete();
            $table->string('category')->default('general'); // technical_review, board_approval, general
            $table->string('stored_filename');
            $table->string('original_filename');
            $table->string('file_path');
            $table->string('disk')->default('local_private');
            $table->string('mime_type')->nullable();
            $table->bigInteger('file_size_bytes')->default(0);
            $table->string('checksum_sha256')->nullable();
            $table->foreignId('uploaded_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('technical_review_approvals');
    }
};
