<?php

namespace Database\Seeders;

use App\Models\ProfileUpdateRequest;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProfileUpdateRequestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::where('id', '!=', 1)->inRandomOrder()->limit(10)->get();

        foreach($users as $user) {
            ProfileUpdateRequest::create([
                'user_id' => $user->id,
                'first_name' => fake()->firstName(),
                'middle_name' => fake()->lastName(),
                'surname' => fake()->lastName(),
                'suffix' => fake()->randomElement([null, fake()->suffix()]),
                'request_id' => '2c2618b7-cc91-41b9-b32d-c21760b9d55f',
                'liveness_score' => 0.99999048954487,
                'card_sanity_score' => 1,
                'card_tampering_score' => 1,
                'liveness_req_id' => '2f367c7e-b276-4d18-a660-a5ccf7e4dce7',
                'card_sanity_req_id' => '6ab88169-0565-4ee1-ae4f-2b5f1774c89e',
                'selfie_sanity_req_id' => 'ad339972-8032-4104-b7ee-8e654412c362',
                'card_tampering_req_id' => 'ffaa4a94-c8ea-4906-b376-7ef295967aa8',
                'selfie_image_id' => 'ffe45585-0336-48c2-913b-d66326eea733',
                'front_card_image_id' => '7d8a9b05-42f4-42f0-9fce-c684f2db6d45',
            ]);
        }
    }
}
