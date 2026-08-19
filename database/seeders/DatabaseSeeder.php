<?php

namespace Database\Seeders;

use App\Models\Area;
use App\Models\Parameter;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleAndPermissionSeeder::class,
            CategorySeeder::class,
        ]);

        $adminRole = Role::where('name', 'admin')->first();
        $facultyRole = Role::where('name', 'faculty')->first();
        $accreditorRole = Role::where('name', 'accreditor')->first();

        // 1. Admin User
        $admin = User::updateOrCreate(
            ['email' => 'admin@cics.marsu.edu.ph'],
            [
                'employee_id' => 'ADM-001',
                'name' => 'Dr. Admin System',
                'password' => Hash::make('password'),
                'role_id' => $adminRole->id,
                'status' => 'active',
            ]
        );
        $admin->assignRole($adminRole);

        // 2. Faculty User
        $faculty = User::updateOrCreate(
            ['email' => 'faculty@cics.marsu.edu.ph'],
            [
                'employee_id' => 'FAC-001',
                'name' => 'Prof. Juan Dela Cruz',
                'password' => Hash::make('password'),
                'role_id' => $facultyRole->id,
                'status' => 'active',
            ]
        );
        $faculty->assignRole($facultyRole);

        // 3. Accreditor User
        $accreditor = User::updateOrCreate(
            ['email' => 'accreditor@cics.marsu.edu.ph'],
            [
                'employee_id' => 'ACC-001',
                'name' => 'Dr. Maria Santos (AACCUP Accreditor)',
                'password' => Hash::make('password'),
                'role_id' => $accreditorRole->id,
                'status' => 'active',
            ]
        );
        $accreditor->assignRole($accreditorRole);

        // 4. Create Sample Areas
        $area1 = Area::create([
            'code' => 'AREA-I',
            'name' => 'Vision, Mission, Goals & Objectives',
            'description' => 'Dissemination, acceptability, and alignment of institutional vision and program objectives.',
            'status' => 'active',
            'created_by' => $admin->id,
        ]);

        $area2 = Area::create([
            'code' => 'AREA-II',
            'name' => 'Faculty',
            'description' => 'Academic qualifications, teaching efficiency, staff development, and performance evaluation.',
            'status' => 'active',
            'created_by' => $admin->id,
        ]);

        $area3 = Area::create([
            'code' => 'AREA-III',
            'name' => 'Curriculum & Instruction',
            'description' => 'Curriculum design, instructional processes, and academic outcomes.',
            'status' => 'active',
            'created_by' => $admin->id,
        ]);

        // 5. Create Sample Parameters (ParameterObserver auto-attaches 3 categories per Parameter)
        Parameter::create([
            'area_id' => $area1->id,
            'code' => '1.1',
            'title' => 'Statement of Vision, Mission, Goals, and Objectives',
            'description' => 'Documentary evidence of VMGO formulation and institutional approval.',
            'sort_order' => 1,
            'status' => 'active',
        ]);

        Parameter::create([
            'area_id' => $area1->id,
            'code' => '1.2',
            'title' => 'Dissemination & Acceptability of VMGO',
            'description' => 'Evidence of stakeholder orientation, website posting, and survey responses.',
            'sort_order' => 2,
            'status' => 'active',
        ]);

        Parameter::create([
            'area_id' => $area2->id,
            'code' => '2.1',
            'title' => 'Academic Qualifications and Faculty Profiles',
            'description' => 'Degrees, transcript of records, licenses, and teaching assignments.',
            'sort_order' => 1,
            'status' => 'active',
        ]);

        // 6. Assign Area I to Faculty (as Handler) and Accreditor (as Accreditor)
        $area1->users()->attach($faculty->id, [
            'assignment_role' => 'handler',
            'assigned_by' => $admin->id,
            'assigned_at' => now(),
        ]);

        $area1->users()->attach($accreditor->id, [
            'assignment_role' => 'accreditor',
            'assigned_by' => $admin->id,
            'assigned_at' => now(),
        ]);
    }
}
