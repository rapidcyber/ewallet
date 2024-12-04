<?php

namespace App\External\Bpi;

use Illuminate\Support\Facades\Log;
use Livewire\Component;

class BpiRedirect extends Component
{
    public function mount()
    {
        $data = request()->all();

        Log::info($data);
    }

    public function render()
    {
        return view('external.bpi.bpi-redirect');
    }
}
