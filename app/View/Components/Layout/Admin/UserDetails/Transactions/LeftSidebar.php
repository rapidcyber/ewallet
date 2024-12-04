<?php

namespace App\View\Components\Layout\Admin\UserDetails\Transactions;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class LeftSidebar extends Component
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
        return view('components.layout.admin.user-details.transactions.left-sidebar');
    }
}
