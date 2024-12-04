<?php

namespace App\Guest\Home;

use App\Models\Transaction;
use Livewire\Attributes\Layout;
use Livewire\Component;

class Home extends Component
{
    public function mount()
    {

    }

    #[Layout('layouts.guest')]
    public function render()
    {
        return view('guest.home.home');
    }
}
