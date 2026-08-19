<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accreditor_evaluations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subfolder_id')->constrained('subfolders')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('rating', 3, 1);
            $table->text('evaluation')->nullable();
            $table->timestamps();

            $table->unique(['subfolder_id', 'user_id'], 'accreditor_evaluation_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accreditor_evaluations');
    }
};