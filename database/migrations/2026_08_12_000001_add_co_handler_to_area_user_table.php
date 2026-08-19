<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Add 'co-handler' to the assignment_role enum on the area_user pivot table.
     * Co-handler has the same access level as handler (Chairman) but is displayed
     * as "Co-Chairman" in the UI.
     */
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'sqlite') {
            // SQLite doesn't enforce ENUM constraints, so we just need to ensure
            // the check constraint (if any) is updated. In practice, SQLite stores
            // ENUMs as TEXT, so 'co-handler' values will work automatically.
            // No schema change needed for SQLite.
        } elseif ($driver === 'pgsql') {
            DB::statement("ALTER TABLE area_user DROP CONSTRAINT IF EXISTS area_user_assignment_role_check");
            DB::statement("ALTER TABLE area_user ADD CONSTRAINT area_user_assignment_role_check CHECK (assignment_role IN ('handler', 'co-handler', 'member', 'accreditor'))");
        } else {
            // MySQL: modify the ENUM column to include the new value
            DB::statement("ALTER TABLE `area_user` MODIFY `assignment_role` ENUM('handler', 'co-handler', 'member', 'accreditor') NOT NULL");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'pgsql') {
            DB::table('area_user')->where('assignment_role', 'co-handler')->delete();
            DB::statement("ALTER TABLE area_user DROP CONSTRAINT IF EXISTS area_user_assignment_role_check");
            DB::statement("ALTER TABLE area_user ADD CONSTRAINT area_user_assignment_role_check CHECK (assignment_role IN ('handler', 'member', 'accreditor'))");
        } elseif ($driver !== 'sqlite') {
            // Remove any co-handler assignments first
            DB::table('area_user')->where('assignment_role', 'co-handler')->delete();
            DB::statement("ALTER TABLE `area_user` MODIFY `assignment_role` ENUM('handler', 'member', 'accreditor') NOT NULL");
        }
    }
};
