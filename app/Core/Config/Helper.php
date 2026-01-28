<?php

namespace App\Core\Config;


use JetBrains\PhpStorm\NoReturn;

class Helper
{
    public static function setMessage($key, $value): void
    {
        $_SESSION['_message'][$key] = $value;
    }

    public static function getMessage($key)
    {
        return $_SESSION['_message'][$key] ?? null;
    }

    #[NoReturn]
    public static function redirect(string $path, array $data = []): void
    {
        if (!headers_sent()) {
            foreach ($data as $key => $value) {
                self::setMessage($key, $value);
            }

            header("Location: {$path}");
        }

        exit;
    }

    public static function hashPassword($password): string
    {
        $option = [];

        return password_hash($password, PASSWORD_DEFAULT, $option);
    }

}