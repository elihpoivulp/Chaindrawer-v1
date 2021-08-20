<?php

namespace CD\Core\DB;

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
        $s = $this->db->prepare("SELECT * FROM $table");
        $s->execute();
        return $s->fetchAll();
    }

    abstract public function tableName(): string;

    abstract public function columns(): array;

    abstract public function primaryKey(): string;
}