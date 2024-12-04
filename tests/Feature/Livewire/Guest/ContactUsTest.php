<?php

namespace Tests\Feature\Livewire\Guest;

use App\Guest\ContactUs\ContactUs;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ContactUsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_saves(): void
    {
        Livewire::test(ContactUs::class)
            ->set('name', 'Pablo Fetalvero')
            ->set('email', 'psfetalvero@repay.ph')
            ->set('subject', 'Test Subject 1234567')
            ->set('message', 'Test Message 1234567')
            ->call('onSend');

        $this->assertDatabaseHas('inquiries', [
            'full_name' => 'Pablo Fetalvero',
            'email' => 'psfetalvero@repay.ph',
            'subject' => 'Test Subject 1234567',
            'message' => 'Test Message 1234567',
        ]);
    }
}
