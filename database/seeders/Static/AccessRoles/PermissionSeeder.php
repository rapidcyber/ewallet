<?php

namespace Database\Seeders\Static\AccessRoles;

use App\Models\Permission;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $actions = [
            'view',
            'create',
            'update',
            'delete',
            'export',
            'approve',
        ];

        foreach ($actions as $action) {
            Permission::firstOrCreate([
                'action' => $action
            ]);
        }
    }
}
