<?php

namespace Tests\Feature\Livewire\User\Dashboard;

use App\Models\User;
use App\User\Dashboard\UserDashboard;
use Livewire\Livewire;
use Tests\TestCase;

class UserDashboardTest extends TestCase
{
    /** @test */
    public function renders_successfully()
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(UserDashboard::class)
            ->assertStatus(200);
    }
}
