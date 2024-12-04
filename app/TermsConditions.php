<?php

namespace App;

use Livewire\Attributes\Layout;
use Livewire\Component;
use Parsedown;

class TermsConditions extends Component
{
    public $terms;

    public function mount()
    {
        $md = file_get_contents(resource_path('markdown/terms.md'));

        $this->terms = (new Parsedown())->text($md);
    }
    #[Layout('layouts.guest')]
    public function render()
    {
        return view('terms-conditions');
    }
}
