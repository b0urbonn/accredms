<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleAndPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            // Area & Parameter Management (Admin)
            'manage-areas',
            'manage-parameters',
            'assign-areas',
            'manage-users',
            'view-audit-logs',
            'configure-settings',

            // Document operations
            'view-assigned-areas',
            'create-subfolders',
            'upload-documents',
            'compress-documents',
            'delete-documents',
            'preview-documents',
            'download-documents',
            'add-remarks',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        // 1. Admin Role
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $adminRole->givePermissionTo(Permission::all());

        // 2. Faculty Role
        $facultyRole = Role::firstOrCreate(['name' => 'faculty', 'guard_name' => 'web']);
        $facultyRole->givePermissionTo([
            'view-assigned-areas',
            'create-subfolders',
            'upload-documents',
            'compress-documents',
            'delete-documents',
            'preview-documents',
            'download-documents',
        ]);

        // 3. Accreditor Role
        $accreditorRole = Role::firstOrCreate(['name' => 'accreditor', 'guard_name' => 'web']);
        $accreditorRole->givePermissionTo([
            'view-assigned-areas',
            'preview-documents',
            'add-remarks',
        ]);
    }
}
