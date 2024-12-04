<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\BookingStatus;
use App\Models\Invoice;
use App\Models\Service;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Booking>
 */
class BookingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $service = Service::active()->inRandomOrder()->first();

        $maxAttempts = 10;
        $attempts = 0;
        $time = null;

        do {
            $attempts++;
            $date = fake()->dateTimeBetween('-1 month', '+1 month');
            $day = Carbon::parse($date)->format('l');
        
            // Decode the service_days JSON string into an array
            $serviceDaysArray = $service->service_days;
        
            // Get all available slots for the chosen day
            if (isset($serviceDaysArray[$day])) {
                $availableSlots = $serviceDaysArray[$day];
            } else {
                // If no slots are available for the chosen day, continue the loop
                continue;
            }
        
            $time = fake()->randomElement($availableSlots);

            if ($time == null) {
                continue;
            }
        
            // Break if maximum attempts reached (to prevent infinite loop)
            if ($attempts >= $maxAttempts) {
                $service = Service::active()->inRandomOrder()->first();
                $maxAttempts = 10;
                $attempts = 0;
            }
        
        } while (Booking::where('service_id', $service->id)
            ->where('service_date', $date)
            ->where('slots', $time)
            ->exists());
        

        if ($date > now()) {
            $booking_status = BookingStatus::where('slug', 'inquiry')->orWhere('slug', 'booked')->inRandomOrder()->first();
        } elseif ($date < now()) {
            $booking_status = BookingStatus::whereNot('slug', 'booked')->inRandomOrder()->first();
        } else {
            $booking_status = BookingStatus::where('slug', 'in_progress')->first();
        }

        return [
            'service_id' => $service->id,
            'slots' => $time,
            'service_date' => $date,
            'message' => fake()->sentence(),
            'invoice_id' => Invoice::factory(),
            'booking_status_id' => $booking_status->id,
        ];
    }
}
