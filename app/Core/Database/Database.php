<?php

namespace App\Core\Database;

use App\Core\Config\Env;
use Illuminate\Database\Capsule\Manager as Capsule;
use PDO;

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

        // Initialize Eloquent (this also creates PDO connection)
        $this->initializeEloquent($driver, $host, $database, $username, $password);
        
        // Get PDO connection from Eloquent (no need for separate initialization)
        $this->connection = $this->capsule->getConnection()->getPdo();
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


}
