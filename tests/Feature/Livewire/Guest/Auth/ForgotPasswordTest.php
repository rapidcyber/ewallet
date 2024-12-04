<?php

namespace Tests\Feature\Livewire\Guest\Auth;

use App\Guest\Auth\ForgotPassword;
use App\Models\PasswordResetCode;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class ForgotPasswordTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_validates_reset_password_inputs()
    {
        Livewire::test(ForgotPassword::class)
            ->set('lookup', '')
            ->set('verification_id', '')
            ->set('code', '')
            ->set('new_password', '')
            ->call('reset_password')
            ->assertHasErrors();
    }

    /** @test */
    public function it_fails_with_invalid_verification_id()
    {
        Livewire::test(ForgotPassword::class)
            ->set('verification_id', 'invalid_id')
            ->set('code', '12345')
            ->set('new_password', 'newpassword')
            ->call('reset_password')
            ->assertHasErrors();
    }

    /** @test */
    public function it_fails_with_invalid_reset_code()
    {
        $user = User::factory()->create();
        $passwordCode = PasswordResetCode::factory()->create([
            'contact' => $user->phone_number,
            'code' => '67890',
            'verification_id' => 'valid_verification_id',
        ]);

        Livewire::test(ForgotPassword::class)
            ->set('verification_id', $passwordCode->verification_id)
            ->set('code', 'invalid_code')
            ->set('new_password', 'newpassword')
            ->call('reset_password')
            ->assertHasErrors();
    }

    /** @test */
    public function it_fails_with_expired_code()
    {
        $user = User::factory()->create();
        $passwordCode = PasswordResetCode::factory()->create([
            'contact' => $user->phone_number,
            'code' => '67890',
            'verification_id' => 'valid_verification_id',
            'expires_at' => now()->subMinutes(10),  // Expired code
        ]);

        Livewire::test(ForgotPassword::class)
            ->set('verification_id', $passwordCode->verification_id)
            ->set('code', $passwordCode->code)
            ->set('new_password', 'newpassword')
            ->call('reset_password')
            ->assertHasErrors();
    }

    /** @test */
    public function it_resets_password_successfully()
    {
        $user = User::factory()->create(['password' => Hash::make('oldpassword')]);
        $passwordCode = PasswordResetCode::factory()->create([
            'contact' => $user->phone_number,
            'code' => '67890',
            'verification_id' => 'valid_verification_id',
            'expires_at' => now()->addMinutes(5),  // Valid code
        ]);

        Livewire::test(ForgotPassword::class)
            ->set('verification_id', $passwordCode->verification_id)
            ->set('code', $passwordCode->code)
            ->set('new_password', 'newpassword')
            ->call('reset_password')
            ->assertHasNoErrors();

        $this->assertTrue(Hash::check('newpassword', $user->fresh()->password));
    }
}
