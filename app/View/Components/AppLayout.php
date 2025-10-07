<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class AppLayout extends Component
{
    /**
     * Rendre la vue du composant.
     */
    public function render(): View
    {
        return view('components.app-layout');
    }
}
