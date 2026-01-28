<?php

namespace App\Core\Contracts;


interface Authenticatable
{
    public static function find(int $id);

    public static function findByCredentials(array $credentials);

    public function getAuthId();

    public function getAuthPassword();
}