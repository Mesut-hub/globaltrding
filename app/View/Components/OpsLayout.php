<?php

namespace App\View\Components;

use Illuminate\View\Component;

class OpsLayout extends Component
{
    public function __construct(public string $title = 'Operations') {}

    public function render()
    {
        return view('ops.layout');
    }
}