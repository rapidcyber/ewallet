<?php

namespace Tests\Feature\Livewire\User\CashOutflow;

use App\Models\User;
use App\User\CashOutflow\UserCashOutflow;
use Livewire\Livewire;
use Tests\TestCase;

class UserCashOutflowTest extends TestCase
{
    /** @test */
    public function renders_successfully()
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(UserCashOutflow::class)
            ->assertStatus(200);
    }
}
