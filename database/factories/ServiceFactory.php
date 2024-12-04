<?php

namespace Database\Factories;

use App\Models\Merchant;
use App\Models\ServiceCategory;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Service>
 */
class ServiceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $start_hour = fake()->numberBetween(6, 9);
        $minute = fake()->randomElement(['00', '30']);
        $end_hour = $start_hour + fake()->numberBetween(7, 10);

        $service_days = [];
        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];

        foreach ($days as $day) {
            $proceed = fake()->boolean(75);
            if ($proceed) {
                $chosen_days[] = $day;
            }
        }
        
        foreach ($chosen_days as $day) {
            $service_days[$day] = $this->generateTimeSlots();
        }

        $approval_status = fake()->boolean(60) ? 'approved' : fake()->randomElement(['review', 'rejected', 'suspended']);

        $date = fake()->dateTimeBetween('-1 month', '-1 day');

        return [
            'merchant_id' => Merchant::factory(),
            'name' => 'Service ' . fake()->word(),
            'description' => fake()->paragraph(2),
            'service_category_id' => ServiceCategory::whereNot('parent', null)->inRandomOrder()->first()->id,
            'service_days' => $service_days,
            'is_active' => $approval_status == 'approved' ? fake()->boolean(80) : false,
            'approval_status' => $approval_status,
            'created_at' => $date,
            'updated_at' => $date,
        ];
    }

    private function generateTimeSlots()
    {
        $slots = [];
        $startTime = Carbon::createFromTime(8, 0); // Start time of the first slot (e.g., 08:00 AM)
        $endTime = Carbon::createFromTime(9, 0);   // End time of the first slot (e.g., 09:00 AM)
        $closingTime = Carbon::createFromTime(17, 0); // End time for the day (e.g., 05:00 PM)

        while ($endTime->lt($closingTime)) {
            $slots[] = [
                'start_time' => $startTime->format('H:i'),
                'end_time' => $endTime->format('H:i'),
            ];

            // Move to the next slot
            $startTime = $endTime->copy(); // The next start time is the current end time
            $endTime = $startTime->copy()->addHour(); // Each slot is 1 hour long
        }

        return $slots;
    }
}
