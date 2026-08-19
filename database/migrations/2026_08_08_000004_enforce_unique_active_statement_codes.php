<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::transaction(function () {
            $duplicateCodes = DB::table('subfolders')
                ->select('parameter_category_id', 'code')
                ->whereNull('deleted_at')
                ->whereNotNull('code')
                ->groupBy('parameter_category_id', 'code')
                ->havingRaw('COUNT(*) > 1')
                ->get();

            foreach ($duplicateCodes as $duplicateCode) {
                $statements = DB::table('subfolders')
                    ->where('parameter_category_id', $duplicateCode->parameter_category_id)
                    ->where('code', $duplicateCode->code)
                    ->whereNull('deleted_at')
                    ->orderBy('id')
                    ->get();

                $canonicalStatement = $statements->shift();

                foreach ($statements as $duplicateStatement) {
                    DB::table('documents')
                        ->where('subfolder_id', $duplicateStatement->id)
                        ->update(['subfolder_id' => $canonicalStatement->id, 'updated_at' => now()]);

                    DB::table('subfolders')
                        ->where('parent_id', $duplicateStatement->id)
                        ->whereNull('deleted_at')
                        ->update(['parent_id' => $canonicalStatement->id, 'updated_at' => now()]);

                    DB::table('subfolders')
                        ->where('id', $duplicateStatement->id)
                        ->update(['deleted_at' => now(), 'updated_at' => now()]);
                }
            }
        });

        Schema::table('subfolders', function (Blueprint $table) {
            $table->string('active_code', 50)
                ->nullable()
                ->storedAs('CASE WHEN deleted_at IS NULL THEN code ELSE NULL END')
                ->after('code');
            $table->unique(['parameter_category_id', 'active_code'], 'subfolders_active_code_unique');
        });
    }

    public function down(): void
    {
        Schema::table('subfolders', function (Blueprint $table) {
            $table->dropUnique('subfolders_active_code_unique');
            $table->dropColumn('active_code');
        });
    }
};