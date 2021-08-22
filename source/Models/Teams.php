<?php

namespace CD\Models;

use CD\Core\DB\BaseDBModel;
use PDO;

class Teams extends BaseDBModel
{

    public function tableName(): string
    {
        return 'AssetTeams';
    }

    public function columns(): array
    {
        return ['*'];
    }

    public function primaryKey(): string
    {
        return 'AssetTeamID';
    }

    public function getTeams()
    {
        $s = $this->db->prepare('CALL GetAllTeams();');
        $s->execute();
        return $s->fetchAll();
    }

    public function getTeamByStatus(int $status)
    {
        $s = $this->db->prepare('CALL GetTeamByStatus(:status)');
        $s->execute(['status' => $status]);
        return $s->fetchAll();
    }

    public function getTeamBySlug(string $slug)
    {
        $s = $this->db->prepare('CALL GetTeamBySlug(:slug)');
        $s->execute(['slug' => $slug]);
        return $s->fetch();
    }

    public function getTeamTypes()
    {
        $s = $this->db->prepare('SELECT * FROM TeamTypes');
        $s->execute();
        return $s->fetchAll();
    }
}