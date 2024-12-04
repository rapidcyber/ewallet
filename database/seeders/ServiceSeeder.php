<?php

namespace Database\Seeders;

use App\Models\Location;
use App\Models\Merchant;
use App\Models\PreviousWork;
use App\Models\Question;
use App\Models\QuestionChoice;
use App\Models\Service;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $merchants = Merchant::all();

        foreach($merchants as $merchant) {
            $services = Service::factory(3)->create([
                'merchant_id' => $merchant->id
            ]);

            foreach ($services as $service) {
                Location::factory()->create([
                    'entity_id' => $service->id,
                    'entity_type' => Service::class
                ]);

                for ($count = 0; $count < fake()->numberBetween(3, 5); $count++) {
                    $question = Question::factory()->create([
                        'entity_id' => $service->id,
                        'entity_type' => Service::class,
                        'order_column' => $count
                    ]);

                    if (in_array($question->type, ['dropdown', 'multiple', 'checkbox'])) {
                        QuestionChoice::factory(fake()->numberBetween(3, 6))->create([
                            'question_id' => $question->id
                        ]);
                    }
                }

                $service->addMediaFromUrl('https://i.imgur.com/S9dsMBX.jpeg')->toMediaCollection('service_images');

                $has_previous_work = fake()->boolean();

                if ($has_previous_work) {
                    for ($count = 1; $count <= fake()->numberBetween(1, 2); $count++) {
                        $previous_work = PreviousWork::factory()->create([
                            'service_id' => $service->id,
                        ]);

                        $previous_work->addMediaFromUrl('https://i.imgur.com/S9dsMBX.jpeg')->toMediaCollection('previous_work_images');
                    }
                }
            }
        }
    }
}
