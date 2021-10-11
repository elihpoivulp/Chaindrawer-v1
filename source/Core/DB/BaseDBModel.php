<?php

namespace CD\Core\DB;

use CD\Models\User;
use PDO;

abstract class BaseDBModel extends DB
{
    protected PDO $db;
    // public ?int $UserID;

    public function __construct()
    {
        $this->db = $this->db();
    }

    public function getAll(): array
    {
        $table = $this->tableName();
        $cols = $this->getColumns();
        $s = $this->db->prepare("SELECT $cols FROM $table");
        $s->execute();
        return $s->fetchAll();
    }

    public function getOne($search_value, string $pk)
    {
        $table = $this->tableName();
        $pk = $pk ?? $this->primaryKey();
        $cols = $this->getColumns();
        $s = $this->db->prepare("SELECT $cols FROM $table WHERE $pk = :value");
        $s->execute(['value' => $search_value]);
        return $s->fetch();
    }

    public function getID()
    {
        $pk = $this->primaryKey();
        return $this->$pk;
    }

    protected function getColumns(): string
    {
        return join(', ', $this->columns());
    }

    protected function conditionArrayToString(array $clause, string $separator = ' AND WHERE '): string
    {
        $clause = array_map(fn($val) => "$val = :$val", array_keys($clause));
        $separator = trim($separator);
        return join( " $separator ", $clause);
    }

    public function setPropertyValues(array $data): void
    {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }

    abstract public function tableName(): string;

    abstract public function columns(): array;

    abstract public function primaryKey(): string;
}