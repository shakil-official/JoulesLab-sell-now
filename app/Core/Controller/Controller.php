<?php

namespace App\Core\Controller;

use App\Core\View\View;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

abstract class Controller
{
    protected View $view;

    public function __construct(View $view)
    {
        $this->view = $view;
    }

    /**
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws LoaderError
     */
    protected function render(string $template, array $data = []): void
    {
        $this->view->render($template, $data);
    }
}
