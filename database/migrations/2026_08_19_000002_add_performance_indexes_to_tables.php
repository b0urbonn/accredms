<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations to speed up queries with B-Tree indexes on PostgreSQL.
     */
    public function up(): void
    {
        // Parameters indexes
        Schema::table('parameters', function (Blueprint $table) {
            $table->index('area_id', 'idx_params_area_id');
            $table->index('status', 'idx_params_status');
        });

        // Subfolders indexes
        Schema::table('subfolders', function (Blueprint $table) {
            $table->index('parameter_category_id', 'idx_subfolders_param_cat_id');
            $table->index('parent_id', 'idx_subfolders_parent_id');
            $table->index('status', 'idx_subfolders_status');
        });

        // Documents indexes
        Schema::table('documents', function (Blueprint $table) {
            $table->index('subfolder_id', 'idx_docs_subfolder_id');
            $table->index('uploaded_by', 'idx_docs_uploaded_by');
            $table->index('status', 'idx_docs_status');
            $table->index('created_at', 'idx_docs_created_at');
        });

        // Document remarks indexes
        Schema::table('document_remarks', function (Blueprint $table) {
            $table->index('document_id', 'idx_doc_remarks_doc_id');
            $table->index('user_id', 'idx_doc_remarks_user_id');
        });

        // Document versions indexes
        Schema::table('document_versions', function (Blueprint $table) {
            $table->index('document_id', 'idx_doc_versions_doc_id');
        });

        // Audit logs indexes
        Schema::table('audit_logs', function (Blueprint $table) {
            $table->index('user_id', 'idx_audit_logs_user_id');
            $table->index('created_at', 'idx_audit_logs_created_at');
        });

        // Additional document requests indexes
        if (Schema::hasTable('additional_document_requests')) {
            Schema::table('additional_document_requests', function (Blueprint $table) {
                $table->index('subfolder_id', 'idx_doc_reqs_subfolder_id');
                $table->index('status', 'idx_doc_reqs_status');
            });
        }

        // Accreditor evaluations indexes
        if (Schema::hasTable('accreditor_evaluations')) {
            Schema::table('accreditor_evaluations', function (Blueprint $table) {
                $table->index('subfolder_id', 'idx_evals_subfolder_id');
                $table->index('user_id', 'idx_evals_user_id');
            });
        }

        // Evidence photos indexes
        if (Schema::hasTable('evidence_photos')) {
            Schema::table('evidence_photos', function (Blueprint $table) {
                $table->index('subfolder_id', 'idx_photos_subfolder_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('parameters', function (Blueprint $table) {
            $table->dropIndex('idx_params_area_id');
            $table->dropIndex('idx_params_status');
        });

        Schema::table('subfolders', function (Blueprint $table) {
            $table->dropIndex('idx_subfolders_param_cat_id');
            $table->dropIndex('idx_subfolders_parent_id');
            $table->dropIndex('idx_subfolders_status');
        });

        Schema::table('documents', function (Blueprint $table) {
            $table->dropIndex('idx_docs_subfolder_id');
            $table->dropIndex('idx_docs_uploaded_by');
            $table->dropIndex('idx_docs_status');
            $table->dropIndex('idx_docs_created_at');
        });

        Schema::table('document_remarks', function (Blueprint $table) {
            $table->dropIndex('idx_doc_remarks_doc_id');
            $table->dropIndex('idx_doc_remarks_user_id');
        });

        Schema::table('document_versions', function (Blueprint $table) {
            $table->dropIndex('idx_doc_versions_doc_id');
        });

        Schema::table('audit_logs', function (Blueprint $table) {
            $table->dropIndex('idx_audit_logs_user_id');
            $table->dropIndex('idx_audit_logs_created_at');
        });

        if (Schema::hasTable('additional_document_requests')) {
            Schema::table('additional_document_requests', function (Blueprint $table) {
                $table->dropIndex('idx_doc_reqs_subfolder_id');
                $table->dropIndex('idx_doc_reqs_status');
            });
        }

        if (Schema::hasTable('accreditor_evaluations')) {
            Schema::table('accreditor_evaluations', function (Blueprint $table) {
                $table->dropIndex('idx_evals_subfolder_id');
                $table->dropIndex('idx_evals_user_id');
            });
        }

        if (Schema::hasTable('evidence_photos')) {
            Schema::table('evidence_photos', function (Blueprint $table) {
                $table->dropIndex('idx_photos_subfolder_id');
            });
        }
    }
};
