<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('technical_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('area_id')->nullable()->constrained('areas')->nullOnDelete();
            $table->string('report_number')->nullable();
            $table->string('title');
            $table->string('program')->nullable();
            $table->string('survey_visit')->nullable();
            $table->text('summary_findings')->nullable();
            $table->text('technical_evaluation')->nullable();
            $table->text('strengths')->nullable();
            $table->text('areas_for_improvement')->nullable();
            $table->text('recommendations')->nullable();
            $table->decimal('overall_score', 4, 2)->nullable();
            $table->string('status')->default('draft'); // draft, under_review, approved, published
            $table->foreignId('prepared_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('board_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('area_id')->nullable()->constrained('areas')->nullOnDelete();
            $table->string('resolution_number')->nullable();
            $table->string('review_title');
            $table->string('program')->nullable();
            $table->string('survey_visit')->nullable();
            $table->string('board_decision')->default('under_board_review'); // accredited_level_1, accredited_level_2, accredited_level_3, accredited_level_4, re_accredited, under_board_review, deferred, not_accredited
            $table->string('validity_period')->nullable();
            $table->text('board_remarks')->nullable();
            $table->text('conditions_set')->nullable();
            $table->date('reviewed_date')->nullable();
            $table->string('status')->default('under_review'); // under_review, resolved, approved, archived
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('board_reviews');
        Schema::dropIfExists('technical_reports');
    }
};
