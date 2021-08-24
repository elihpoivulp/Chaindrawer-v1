<?php

namespace CD\Models;

use CD\Core\DB\BaseDBModel;
use PDO;

class Users extends BaseDBModel
{

    public function tableName(): string
    {
        return 'Users';
    }

    public function columns(): array
    {
        return ['*'];
    }

    public function primaryKey(): string
    {
        return 'UserID';
    }

    public function getAllUsers()
    {
        $s = $this->db->prepare("SELECT * FROM Users");
        $s->setFetchMode(PDO::FETCH_CLASS, User::class);
        $s->execute();
        return $s->fetchAll();
    }
}