<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use App\Models\Profile;
use App\Traits\WithNumberGeneration;
use Illuminate\Database\Seeder;

class TestSeeder extends Seeder
{
    use WithNumberGeneration;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->test_users();
    }


    private function test_users()
    {
        $tests = [
            [
                'username' => 'testusera',
                'email' => 'testuser.a@repay.ph',
                'phone_number' => '639161234567',
                'roles' => ['user', 'verified_user'],
            ],
            [
                'username' => 'testuserb',
                'email' => 'testuser.b@repay.ph',
                'phone_number' => '639167654321',
                'roles' => ['user', 'verified_user'],
            ],
            [
                'username' => 'sampleusera',
                'email' => 'sample.a@repay.ph',
                'phone_number' => '639654101043',
                'roles' => ['user']
            ]
        ];

        foreach ($tests as $test) {
            $user = User::where('username', $test['username'])->first();
            if (empty($user)) {
                $user = User::factory(1)->has(Profile::factory())->create([
                    'username' => $test['username'],
                    'email' => $test['email'],
                    'phone_number' => $test['phone_number'],
                ])->first();
                $user->profile->status = 'verified';
                $user->profile->save();
                foreach($test['roles'] as $role_slug) {
                    $user->roles()->syncWithoutDetaching(Role::where('slug', $role_slug)->first()->id);
                }
            }
        }
    }
}
