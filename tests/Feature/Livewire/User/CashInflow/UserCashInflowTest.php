<?php

namespace Tests\Feature\Livewire\User\CashInflow;

use App\Models\User;
use App\User\CashInflow\UserCashInflow;
use Livewire\Livewire;
use Tests\TestCase;

class UserCashInflowTest extends TestCase
{
    /** @test */
    public function it_renders_the_correct_cash_inflow_data()
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(UserCashInflow::class)
            ->assertStatus(200);
    }
}
