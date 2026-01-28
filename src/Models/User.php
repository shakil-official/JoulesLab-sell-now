<?php

namespace SellNow\Models;

use App\Core\Contracts\Authenticatable;
use App\Core\Database\Model;
use Exception;

class User extends Model implements Authenticatable
{
    protected string $table = 'users';


    public function getAuthId(): int
    {
        return (int) $this->id;
    }

    public function getAuthPassword(): string
    {
        return $this->password;
    }

    /**
     * @throws Exception
     */
    public static function findByCredentials(array $credentials): ?self
    {
        if (!isset($credentials['email'])) {
            return null;
        }

        $row = static::query()
            ->where(['email' => $credentials['email']])
            ->first(); // array

        if (!$row) {
            return null;
        }

        $user = new static();

        foreach ($row as $key => $value) {
            $user->$key = $value;
        }

        return $user;
    }

    public function getUsername()
    {
        return $this->username;
    }
}