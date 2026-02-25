<?php

namespace App\Core\Database;

use App\Core\Config\Env;
use Illuminate\Database\Capsule\Manager as Capsule;
use PDO;
use PDOException;

class Database
{
    private static ?Database $instance = null;
    private PDO $connection;
    private ?Capsule $capsule = null;

    private function __construct()
    {
        $driver = Env::get('DB_CONNECTION') ?? 'sqlite';
        $host = Env::get('DB_HOST') ?? '127.0.0.1';
        $database = Env::get('DB_DATABASE') ?? 'sellnow';
        $username = Env::get('DB_USERNAME') ?? 'root';
        $password = Env::get('DB_PASSWORD') ?? '';

        // Initialize PDO connection (for legacy compatibility)
        $this->initializePdoConnection($driver, $host, $database, $username, $password);
        
        // Initialize Eloquent (for modern ORM)
        $this->initializeEloquent($driver, $host, $database, $username, $password);
    }

    private function initializePdoConnection(string $driver, string $host, string $database, string $username, string $password): void
    {
        try {
            switch ($driver) {
                case 'mysql':
                    $this->connection = new PDO("mysql:host=" . ($host) . ";dbname=" . ($database), $username, $_ENV['DB_PASSWORD'] ?? '');
                    break;

                case 'pgsql':
                    $this->connection = new PDO("pgsql:host=" . ($host) . ";dbname=" . ($database), $username, $password);
                    break;

                default: // sqlite fallback
                    $dbPath = __DIR__ . '/../../../database/database.sqlite';
                    $this->connection = new PDO("sqlite:" . $dbPath);
            }

            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }

    private function initializeEloquent(string $driver, string $host, string $database, string $username, string $password): void
    {
        $this->capsule = new Capsule;

        switch ($driver) {
            case 'mysql':
                $this->capsule->addConnection([
                    'driver'    => 'mysql',
                    'host'      => $host,
                    'database'  => $database,
                    'username'  => $username,
                    'password'  => $password,
                    'charset'   => 'utf8mb4',
                    'collation' => 'utf8mb4_unicode_ci',
                    'prefix'    => '',
                ]);
                break;

            case 'pgsql':
                $this->capsule->addConnection([
                    'driver'    => 'pgsql',
                    'host'      => $host,
                    'database'  => $database,
                    'username'  => $username,
                    'password'  => $password,
                    'charset'   => 'utf8',
                    'prefix'    => '',
                ]);
                break;

            default: // sqlite
                $dbPath = __DIR__ . '/../../../database/database.sqlite';
                $this->capsule->addConnection([
                    'driver'    => 'sqlite',
                    'database'  => $dbPath,
                    'prefix'    => '',
                ]);
        }

        // Set as global and boot Eloquent
        $this->capsule->setAsGlobal();
        $this->capsule->bootEloquent();
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

    public function getCapsule(): ?Capsule
    {
        return $this->capsule;
    }

    public function getEloquentConnection(): \Illuminate\Database\Connection
    {
        return $this->capsule?->getConnection();
    }
}
