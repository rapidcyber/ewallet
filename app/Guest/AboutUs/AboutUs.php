<?php

namespace App\Guest\AboutUs;

use Livewire\Attributes\Layout;
use Livewire\Component;

class AboutUs extends Component
{
    #[Layout('layouts.guest')]
    public function render()
    {
        return view('guest.about-us.about-us');
    }
}
