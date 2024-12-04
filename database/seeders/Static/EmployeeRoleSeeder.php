<?php

namespace Database\Seeders\Static;

use App\Models\EmployeeRole;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EmployeeRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            'Owner',
            'Admin',
            'Human Resource',
            'Accounting',
            'Employee'
        ];

        foreach($data as $role) {
            EmployeeRole::firstOrCreate([
                'name' => $role
            ], [
                'slug' => str($role)->slug('_'),
            ]);
        }
    }
}
