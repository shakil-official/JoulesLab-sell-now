<?php

namespace App\Core\Services;

use App\Core\Config\Csrf;

class CsrfService
{
    public function generate(): string
    {
        return Csrf::generate();
    }

    public function validate(?string $token): bool
    {
        return Csrf::validate($token);
    }

    public function forget(): void
    {
        Csrf::forget();
    }
}
