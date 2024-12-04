<?php

namespace Database\Seeders\Static;

use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            'Administrator',
            'Merchant',
            'User',
            'Verified User',
        ];

        foreach($data as $role) {
            Role::firstOrCreate([
                'name' => $role
            ], [
                'slug' => str($role)->slug('_'),
            ]);
        }
    }
}
