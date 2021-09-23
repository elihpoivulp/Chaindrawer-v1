<?php

namespace CD\Models;

use CD\Core\DB\DB;
use PDO;

class CronJobs extends DB
{
    private PDO $db;

    public function __construct()
    {
        $this->db = $this->db();
    }

    public function update_team_data(int $id, array $data)
    {
//        $s = $this->db->prepare('')
    }

    public function test(): bool
    {
        $s = $this->db->prepare("INSERT INTO test (value) VALUES (?)");
        return $s->execute(['nice']);
    }
}