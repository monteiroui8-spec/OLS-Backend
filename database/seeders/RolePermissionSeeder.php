<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'manage_users', 'view_users',
            'manage_courses', 'view_courses',
            'manage_classes', 'view_classes',
            'manage_payments', 'view_payments',
            'manage_exams', 'view_exams',
            'manage_grades', 'view_grades',
            'manage_documents', 'view_documents',
            'manage_settings',
            'view_reports',
            'manage_fouls',
        ];

        foreach ($permissions as $name) {
            Permission::firstOrCreate([
                'name' => $name,
                'guard_name' => 'web',
            ]);
        }

        $admin = Role::firstOrCreate([
            'name' => 'admin',
            'guard_name' => 'web',
        ]);
        $admin->syncPermissions(Permission::all());

        $teacher = Role::firstOrCreate([
            'name' => 'teacher',
            'guard_name' => 'web',
        ]);
        $teacher->syncPermissions([
            'view_classes',
            'manage_exams',
            'view_exams',
            'manage_grades',
            'view_grades',
            'view_documents',
            'manage_documents',
            'manage_fouls',
        ]);

        $student = Role::firstOrCreate([
            'name' => 'student',
            'guard_name' => 'web',
        ]);
        $student->syncPermissions([
            'view_courses',
            'view_exams',
            'view_grades',
            'view_payments',
            'view_documents',
        ]);
    }
}

