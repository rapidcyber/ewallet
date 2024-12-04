<?php

namespace App\Admin\Components;

use Livewire\Component;

class AdminTopNavigation extends Component
{
    public $modalOpen = false;
    public $modalDetails = [];

    protected $listeners = ['open-modal' => 'openModal'];

    public function handleDetailsModal($visible)
    {
        $this->modalOpen = $visible;
    } 
    private function fetchEntity($type, $id)
    {
        // Fetch your entity logic
    }
}
