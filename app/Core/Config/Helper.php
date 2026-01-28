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

    /**
     * Upload file helper
     *
     * @param string $inputName   Name of the file input (e.g. 'image')
     * @param string $uploadDir   Absolute path to upload directory
     * @param string|null $prefix Optional prefix for the filename
     *
     * @return string|null Relative path to uploaded file or null if no file uploaded
     */
    public static function uploadFile(string $inputName, string $uploadDir, ?string $prefix = null): ?string
    {
        if (!isset($_FILES[$inputName]) || $_FILES[$inputName]['error'] !== 0) {
            return null;
        }

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $filename = ($prefix ?? time()) . '_' . basename($_FILES[$inputName]['name']);
        $destination = rtrim($uploadDir, '/') . '/' . $filename;

        if (move_uploaded_file($_FILES[$inputName]['tmp_name'], $destination)) {
            // Return relative path for DB/storage
            return 'uploads/' . $filename;
        }

        return null;
    }

    public static function slug($title, $min = 1000, $max = 9999): string
    {
        return  strtolower(str_replace(' ', '-', $title)) . '-' . rand($min, $max);
    }

}