<?php

namespace App\Core\Services;

use App\Core\Database\Database;

class DatabaseService
{
    private ?\PDO $connection = null;

    public function getConnection(): \PDO
    {
        if ($this->connection === null) {
            $this->connection = Database::getInstance()->getConnection();
        }
        return $this->connection;
    }
}
