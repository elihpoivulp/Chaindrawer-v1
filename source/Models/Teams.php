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
        $s->setFetchMode(PDO::FETCH_CLASS, Team::class);
        $s->execute(['slug' => $slug]);
        return $s->fetch();
    }

    public function getTeamTypes()
    {
        $s = $this->db->prepare('SELECT * FROM TeamTypes');
        $s->execute();
        return $s->fetchAll();
    }

    public function getRoninAddresses()
    {
        $s = $this->db->prepare("SELECT AssetTeamID AS id, AxieTeamTrackerAddress AS ronin, AxieTeamCurrentSLPBalance AS latest_balance FROM AxieTeams");
        $s->execute();
        return $s->fetchAll();
    }

    public function updateTeamData($id, $data): bool
    {
        $vals = '';
        foreach ($data as $key => $value) {
            $vals .= $key . ' = :' . strtolower($key) . ', ';
        }
        $vals = rtrim($vals, ', ');
        $sql = "UPDATE AxieTeams SET $vals WHERE AssetTeamID = :id";
        $s = $this->db->prepare($sql);
        foreach ($data as $key => $row) {
            $s->bindValue(':' . strtolower($key), $row);
        }
        $s->bindValue(':id', $id);
        return $s->execute();
    }

    public function updateDailySLP($id, $value): bool
    {
        $sql = "INSERT INTO DailySLPGrind (AxieTeamID, DailySLPGrindAmount) VALUES (:id, :val)";
        $s = $this->db->prepare($sql);
        $s->bindValue(':id', $id);
        $s->bindValue(':val', $value);
        return $s->execute();
    }
}