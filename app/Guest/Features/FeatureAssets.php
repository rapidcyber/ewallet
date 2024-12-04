<?php

namespace App\Guest\Features;

use Livewire\Attributes\Layout;
use Livewire\Component;

class FeatureAssets extends Component
{
    #[Layout('layouts.guest')]
    public function render()
    {
        return view('guest.features.feature-assets');
    }
}
