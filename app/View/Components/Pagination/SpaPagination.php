<?php

namespace App\View\Components\Pagination;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class SpaPagination extends Component
{
    public $hasPages = false;
    /**
     * Create a new component instance.
     */
    public function __construct($hasPages)
    {
        $this->hasPages = $hasPages;
    }

    public function shouldRender()
    {
        return $this->hasPages;
    }
    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.pagination.spa-pagination');
    }
}
