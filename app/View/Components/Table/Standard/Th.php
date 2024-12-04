<?php

namespace App\View\Components\Table\Standard;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Th extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.table.standard.th');
    }
}