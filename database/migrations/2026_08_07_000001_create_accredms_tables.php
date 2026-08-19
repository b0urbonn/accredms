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
        // 1. Areas
        Schema::create('areas', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();
            $table->string('name', 255);
            $table->text('description')->nullable();
            $table->enum('status', ['active', 'inactive', 'submission_ready'])->default('active');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        // 2. Area Assignments (Pivot table)
        Schema::create('area_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('area_id')->constrained('areas')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('assignment_role', ['handler', 'member', 'accreditor']);
            $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('assigned_at')->useCurrent();
            $table->timestamps();

            $table->unique(['area_id', 'user_id', 'assignment_role'], 'area_user_role_unique');
        });

        // 3. Parameters
        Schema::create('parameters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('area_id')->constrained('areas')->cascadeOnDelete();
            $table->string('code', 20);
            $table->string('title', 255);
            $table->text('description')->nullable();
            $table->smallInteger('sort_order')->default(0);
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
            $table->softDeletes();
        });

        // 4. Categories (Master lookup)
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();
            $table->string('slug', 100)->unique();
            $table->tinyInteger('sort_order')->default(0);
            $table->timestamps();
        });

        // 5. Parameter Categories (Junction node - fixed 3 per parameter)
        Schema::create('parameter_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parameter_id')->constrained('parameters')->cascadeOnDelete();
            $table->foreignId('category_id')->constrained('categories')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['parameter_id', 'category_id'], 'param_cat_unique');
        });

        // 6. Subfolders
        Schema::create('subfolders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parameter_category_id')->constrained('parameter_categories')->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('subfolders')->nullOnDelete();
            $table->string('code', 50)->nullable();
            $table->string('name', 255);
            $table->text('documents_needed')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('status', ['active', 'archived'])->default('active');
            $table->timestamps();
            $table->softDeletes();
        });

        // 7. Documents
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subfolder_id')->constrained('subfolders')->cascadeOnDelete();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('original_filename', 255);
            $table->string('stored_filename', 255);
            $table->string('disk', 50)->default('local_private');
            $table->string('file_path', 500);
            $table->string('mime_type', 100)->default('application/pdf');
            $table->bigInteger('file_size_bytes');
            $table->bigInteger('original_size_bytes')->nullable();
            $table->boolean('is_compressed')->default(false);
            $table->enum('compression_status', ['none', 'pending', 'processing', 'done', 'failed'])->default('none');
            $table->string('checksum_sha256', 64);
            $table->smallInteger('version')->default(1);
            $table->enum('status', ['active', 'archived'])->default('active');
            $table->timestamps();
            $table->softDeletes();
        });

        // 8. Document Versions
        Schema::create('document_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained('documents')->cascadeOnDelete();
            $table->smallInteger('version');
            $table->string('file_path', 500);
            $table->bigInteger('file_size_bytes');
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('created_at')->useCurrent();
        });

        // 9. Audit Logs
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action', 100);
            $table->string('auditable_type', 150)->nullable();
            $table->unsignedBigInteger('auditable_id')->nullable();
            $table->string('description', 255)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 255)->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        // 10. Document Remarks
        Schema::create('document_remarks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained('documents')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->text('remark');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_remarks');
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('document_versions');
        Schema::dropIfExists('documents');
        Schema::dropIfExists('subfolders');
        Schema::dropIfExists('parameter_categories');
        Schema::dropIfExists('categories');
        Schema::dropIfExists('parameters');
        Schema::dropIfExists('area_user');
        Schema::dropIfExists('areas');
    }
};
