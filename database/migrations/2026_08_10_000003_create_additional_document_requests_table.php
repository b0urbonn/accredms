<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('additional_document_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subfolder_id')->constrained()->cascadeOnDelete();
            $table->foreignId('requested_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->text('requested_documents')->nullable();
            $table->text('remarks');
            $table->date('due_date')->nullable();
            $table->enum('status', ['open', 'resubmitted', 'fulfilled', 'cancelled'])->default('open');
            $table->timestamp('fulfilled_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('additional_document_requests');
    }
};