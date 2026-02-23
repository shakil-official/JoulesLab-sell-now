<?php

namespace App\Core\Route;

use App\Core\View\View;

class ViewController
{
    protected View $view;

    public function __construct(View $view)
    {
        $this->view = $view;
    }

    public function render(Request $request): void
    {
        // This method is handled by the Router directly
        // This controller is just a placeholder for route registration
    }
}
