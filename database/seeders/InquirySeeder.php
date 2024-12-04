<?php

namespace Database\Seeders;

use App\Models\Inquiry;
use App\Traits\WithNumberGeneration;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class InquirySeeder extends Seeder
{
    use WithNumberGeneration;
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        for ($i = 0; $i < 30; $i++) {
            $ticket_no = $this->generate_ticket_number('inquiry');

            do {
                sleep(1);
                $ticket_no = $this->generate_ticket_number('inquiry');
            } while (Inquiry::where('ticket_no', $ticket_no)->exists());

            $inquiry = Inquiry::factory()->create([
                'ticket_no' => $ticket_no
            ]);
        }
    }
}
