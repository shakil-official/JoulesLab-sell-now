<?php

namespace App\Core\View;

use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class View
{
    protected Environment $twig;

    public function __construct(Environment $twig)
    {
        $this->twig = $twig;
    }

    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     */
    public function render(string $view, array $data = []): string
    {
        return $this->twig->render($view . '.html.twig', $data);
    }

    /**
     * Render and output content directly (for backward compatibility)
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     */
    public function output(string $view, array $data = []): void
    {
        echo $this->render($view, $data);
    }
}
