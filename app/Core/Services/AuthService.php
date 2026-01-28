<?php

namespace App\Core\Services;

use App\Core\Contracts\Authenticatable;
use Exception;

class AuthService
{
    protected static string $model;
    private const SESSION_KEY = 'auth';

    /**
     * @throws Exception
     */
    public static function use(string $model): void
    {
        if (!is_subclass_of($model, Authenticatable::class)) {
            throw new Exception("Model must implement Authenticatable");
        }

        static::$model = $model;
    }

    public static function attempt(array $credentials): bool
    {
        $model = static::$model;

        $user = $model::findByCredentials($credentials);

        if (!$user) {
            return false;
        }


        if (!password_verify($credentials['password'], $user->getAuthPassword())) {
            return false;
        }

        session_regenerate_id(true);

        $_SESSION[self::SESSION_KEY] = [
            'model' => $model,
            'id' => $user->getAuthId(),
            'user_id' => $user->getAuthId(),
            'username' => $user->getUsername(),
        ];

        return true;
    }

    public static function user()
    {
        if (!isset($_SESSION[self::SESSION_KEY])) {
            return null;
        }

        $model = $_SESSION[self::SESSION_KEY]['model'];

        return $model::find($_SESSION[self::SESSION_KEY]['id'], [
            'username', 'email', 'id'
        ]);
    }

    public static function logout(): void
    {
        if (isset($_SESSION[self::SESSION_KEY])) {
            unset($_SESSION[self::SESSION_KEY]);
        }
        session_regenerate_id(true); // prevent fixation
    }

    public static function userId()
    {
        return $_SESSION[self::SESSION_KEY]['id'] ?? null;
    }

}