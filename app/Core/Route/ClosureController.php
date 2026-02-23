<?php

namespace App\Core\Route;

use App\Core\View\View;

class ClosureController
{
    protected View $view;
    protected $callback;

    public function __construct(View $view, $callback = null)
    {
        $this->view = $view;
        $this->callback = $callback;
    }

    public function handle(Request $request): void
    {
        if ($this->callback && is_callable($this->callback)) {
            call_user_func($this->callback, $request);
        }
    }
}
