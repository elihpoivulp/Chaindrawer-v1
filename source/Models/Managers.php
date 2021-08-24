<?php

namespace CD\Models;

use CD\Core\DB\BaseDBModel;
use PDO;

class Managers extends BaseDBModel
{

    public function tableName(): string
    {
        return 'ManagerAccounts';
    }

    public function columns(): array
    {
        return ['*'];
    }

    public function primaryKey(): string
    {
        return 'ManagerAccountID';
    }

    public function getAllManagers()
    {
        $s = $this->db->prepare("SELECT * FROM ManagersList");
        $s->setFetchMode(PDO::FETCH_CLASS, Manager::class);
        $s->execute();
        return $s->fetchAll();
    }
}