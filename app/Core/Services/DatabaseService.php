<?php

namespace App\Core\Services;

use App\Core\Database\Database;
use App\Core\Database\Model;

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

    public function initializeModelConnection(): void
    {
        Model::setConnection($this->getConnection());
    }
}
