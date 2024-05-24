<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\RolesAndPermissions;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run()
    {
        $roles_and_permissions = [
            ['role_id' => '1', 'permission_id' => '1', 'created_by' => '1'],
            ['role_id' => '1', 'permission_id' => '2', 'created_by' => '1'],
            ['role_id' => '1', 'permission_id' => '3', 'created_by' => '1'],
            ['role_id' => '1', 'permission_id' => '4', 'created_by' => '1'],
            ['role_id' => '1', 'permission_id' => '5', 'created_by' => '1'],
            ['role_id' => '1', 'permission_id' => '6', 'created_by' => '1'],
            ['role_id' => '1', 'permission_id' => '7', 'created_by' => '1'],
            ['role_id' => '1', 'permission_id' => '8', 'created_by' => '1'],
            ['role_id' => '1', 'permission_id' => '9', 'created_by' => '1'],
            ['role_id' => '1', 'permission_id' => '10', 'created_by' => '1'],
            ['role_id' => '1', 'permission_id' => '11', 'created_by' => '1'],
            ['role_id' => '1', 'permission_id' => '12', 'created_by' => '1'],
            ['role_id' => '1', 'permission_id' => '13', 'created_by' => '1'],
            ['role_id' => '1', 'permission_id' => '14', 'created_by' => '1'],
            ['role_id' => '1', 'permission_id' => '15', 'created_by' => '1'],
            ['role_id' => '1', 'permission_id' => '16', 'created_by' => '1'],
            ['role_id' => '1', 'permission_id' => '17', 'created_by' => '1'],
            ['role_id' => '1', 'permission_id' => '18', 'created_by' => '1'],
            ['role_id' => '2', 'permission_id' => '1', 'created_by' => '1'],
            ['role_id' => '2', 'permission_id' => '2', 'created_by' => '1'],
            ['role_id' => '2', 'permission_id' => '3', 'created_by' => '1'],
            ['role_id' => '3', 'permission_id' => '1', 'created_by' => '1'],
        ];

        foreach ($roles_and_permissions as $role_and_permission) {
            RolesAndPermissions::create($role_and_permission);
        }
    }
}
