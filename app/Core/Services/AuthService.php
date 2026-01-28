<?php

namespace App\Core\Services;

use App\Core\Contracts\Authenticatable;
use Exception;

class AuthService
{
    protected static string $model;

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

//        var_dump($user->getAuthPassword());
//        die();

        if (!password_verify($credentials['password'], $user->getAuthPassword())) {
            return false;
        }

        session_regenerate_id(true);

        $_SESSION['auth'] = [
            'model' => $model,
            'id'    => $user->getAuthId(),
        ];

        return true;
    }

    public static function user(): ?object
    {
        if (!isset($_SESSION['auth'])) {
            return null;
        }

        $model = $_SESSION['auth']['model'];
        return $model::find($_SESSION['auth']['id']);
    }

    public static function logout(): void
    {
        session_destroy();
    }
}