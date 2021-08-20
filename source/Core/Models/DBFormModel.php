<?php


namespace CD\Core\Models;

use CD\Core\DB\QueryLib;
use PDO;

abstract class DBFormModel extends QueryLib
{
    protected PDO $db;

    public function __construct()
    {
        $this->db = $this->db();
    }
    public function setPropertyValues(array $data): void
    {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }
}