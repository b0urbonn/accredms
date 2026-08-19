<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();
        if ($driver === 'mysql' || $driver === 'mariadb') {
            DB::statement("ALTER TABLE audit_logs MODIFY description TEXT NULL");
            DB::statement("ALTER TABLE subfolders MODIFY name TEXT NOT NULL");
            DB::statement("ALTER TABLE parameters MODIFY title TEXT NOT NULL");
        } elseif ($driver === 'pgsql') {
            DB::statement("ALTER TABLE audit_logs ALTER COLUMN description TYPE TEXT");
            DB::statement("ALTER TABLE subfolders ALTER COLUMN name TYPE TEXT");
            DB::statement("ALTER TABLE parameters ALTER COLUMN title TYPE TEXT");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();
        if ($driver === 'mysql' || $driver === 'mariadb') {
            DB::statement("ALTER TABLE audit_logs MODIFY description VARCHAR(255) NULL");
            DB::statement("ALTER TABLE subfolders MODIFY name VARCHAR(255) NOT NULL");
            DB::statement("ALTER TABLE parameters MODIFY title VARCHAR(255) NOT NULL");
        } elseif ($driver === 'pgsql') {
            DB::statement("ALTER TABLE audit_logs ALTER COLUMN description TYPE VARCHAR(255)");
            DB::statement("ALTER TABLE subfolders ALTER COLUMN name TYPE VARCHAR(255)");
            DB::statement("ALTER TABLE parameters ALTER COLUMN title TYPE VARCHAR(255)");
        }
    }
};
