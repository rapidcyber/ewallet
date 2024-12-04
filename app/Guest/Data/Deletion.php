<?php

namespace App\Guest\Data;

use Livewire\Attributes\Layout;
use Livewire\Component;

class Deletion extends Component
{
    #[Layout('layouts.guest')]
    public function render()
    {
        return view('guest.data.deletion');
    }
}
