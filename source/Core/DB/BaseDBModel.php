<?php

namespace CD\Core\DB;

use PDO;

class BaseDBModel extends DB
{
    protected PDO $db;
    // public ?int $UserID;

    public function __construct()
    {
        $this->db = $this->db();
    }
}