<?php

namespace App;

use Livewire\Attributes\Layout;
use Livewire\Component;
use Parsedown;

class PrivacyPolicy extends Component
{
    public $policy;

    public function mount()
    {
        $md = file_get_contents(resource_path('markdown/policy.md'));

        $this->policy = (new Parsedown())->text($md);
    }

    #[Layout('layouts.guest')]
    public function render()
    {
        return view('privacy-policy');
    }
}
