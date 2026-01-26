<?php

namespace App\Core\Database;

use PDO;
use Exception;

abstract class Model
{
    protected static PDO $db;

    protected string $table;
    protected array $columns = ['*'];
    protected array $wheres = [];
    protected ?int $limit = null;

    // Static connection setup
    public static function setConnection(PDO $pdo): void
    {
        static::$db = $pdo;
    }

    // Fluent query builder
    public static function query(): static
    {
        return new static();
    }

    public static function select(array|string ...$columns): static
    {
        $instance = static::query();

        if (!empty($columns)) {
            $instance->columns = is_array($columns[0]) ? $columns[0] : $columns;
        }

        return $instance;
    }

    /**
     * Add WHERE condition - supports multiple styles
     *
     * ->where('column', 'value')
     * ->where('column', '>', 'value')
     * ->where(['col1' => 'val1', 'col2' => 'val2'])
     */
    public function where(...$args): static
    {
        $count = count($args);

        if ($count === 1 && is_array($args[0])) {
            // where(['col' => 'val', 'col2' => 'val2'])
            foreach ($args[0] as $column => $value) {
                $this->wheres[] = [$column, '=', $value];
            }
        } elseif ($count === 2) {
            // where('column', 'value')
            $this->wheres[] = [$args[0], '=', $args[1]];
        } elseif ($count === 3) {
            // where('column', 'operator', 'value')
            $this->wheres[] = [$args[0], $args[1], $args[2]];
        } else {
            throw new Exception("Invalid where() arguments");
        }

        return $this;
    }

    public function limit(int $limit): static
    {
        $this->limit = $limit;
        return $this;
    }

    public function first(): ?array
    {
        $this->limit = 1;
        $results = $this->get();
        return $results[0] ?? null;
    }

    /**
     * @throws Exception
     */
    public function get(): array
    {
        if (!isset(static::$db)) {
            throw new Exception("Database connection not set. Call Model::setConnection() first.");
        }

        $sql = $this->buildSelectQuery();
        $bindings = $this->getBindings();

        $stmt = static::$db->prepare($sql);
        $stmt->execute($bindings);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    protected function buildSelectQuery(): string
    {
        if (empty($this->table)) {
            $className = (new \ReflectionClass($this))->getShortName();
            $this->table = strtolower($className) . 's';
        }

        $sql = "SELECT " . implode(', ', $this->columns) . " FROM {$this->table}";

        if (!empty($this->wheres)) {
            $conditions = [];
            foreach ($this->wheres as [$column, $operator, $value]) {
                $conditions[] = "$column $operator ?";
            }
            $sql .= " WHERE " . implode(' AND ', $conditions);
        }

        if ($this->limit !== null) {
            $sql .= " LIMIT " . (int)$this->limit;
        }

        return $sql;
    }

    protected function getBindings(): array
    {
        return array_column($this->wheres, 2); // third element = value
    }

    // shortcut for all records
    public static function all(): array
    {
        return static::query()->get();
    }

    // find by primary key (assuming 'id')

    /**
     * @throws Exception
     */
    public static function find(int $id): ?array
    {
        return static::query()->where('id', $id)->first();
    }
}