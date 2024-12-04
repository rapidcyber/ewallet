<?php

namespace Database\Seeders;

use App\Models\Profile;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::factory(3)->has(Profile::factory())->create();
        foreach ($users as $user) {
            $user->roles()->attach(Role::where('slug', str('user')->slug())->first()->id);
        }
    }
}
