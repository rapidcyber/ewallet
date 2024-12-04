<?php

namespace App\Components\Layout\Guest;

use Livewire\Attributes\Locked;
use Livewire\Component;

class GuestNavigation extends Component
{
    public $whiteLogo;
    public $whiteText;

    #[Locked]
    public $button_clickable = true;

    public function mount($whiteLogo = false, $whiteText = false)
    {
        $this->whiteLogo = $whiteLogo;
        $this->whiteText = $whiteText;
    }

    public function redirect_sign_in()
    {
        return redirect()->route('sign-in');
    }

    public function logout()
    {
        if (!auth()->check()) {
            return redirect()->route('home');
        }

        auth()->logout();

        session()->invalidate();
        session()->regenerateToken();

        $this->button_clickable = false;

        return redirect()->route('home');
    }

    public function render()
    {
        return view('components.layout.guest.guest-navigation');
    }
}
