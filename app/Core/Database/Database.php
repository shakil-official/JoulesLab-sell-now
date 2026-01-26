<?php

namespace App\Core\Database;

use App\Core\Config\Env;
use PDO;
use PDOException;

class Database
{
    private static ?Database $instance = null;
    private PDO $connection;

    private function __construct()
    {
        $driver = Env::get('DB_CONNECTION') ?? 'sqlite';
        $host = Env::get('DB_HOST') ?? '127.0.0.1';
        $database = Env::get('DB_DATABASE') ?? 'sellnow';
        $username = Env::get('DB_USERNAME') ?? 'root';
        $password = Env::get('DB_PASSWORD') ?? '';


        try {
            switch ($driver) {
                case 'mysql':
                    $this->connection = new PDO("mysql:host=" . ($host) . ";dbname=" . ($database), $username, $_ENV['DB_PASSWORD'] ?? ''
                    );
                    break;

                case 'pgsql':
                    $this->connection = new PDO("pgsql:host=" . ($host) . ";dbname=" . ($database), $username, $password);
                    break;

                default: // sqlite fallback
                    $dbPath = __DIR__ . '/../../database/database.sqlite';
                    $this->connection = new PDO("sqlite:" . $dbPath);
            }

            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }

    public static function getInstance(): Database
    {
        if (!self::$instance) {
            self::$instance = new Database();
        }

        return self::$instance;
    }

    public function getConnection(): PDO
    {
        return $this->connection;
    }
}
