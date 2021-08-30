<?php

namespace CD\Models;

use CD\Core\DB\BaseDBModel;

class AxieTeam extends BaseDBModel
{

    protected int $AssetTeamID;

    public function tableName(): string
    {
        return 'AxieTeams';
    }

    public function columns(): array
    {
        return ['*'];
    }

    public function primaryKey(): string
    {
        return 'AssetTeamID';
    }

    public function getDailySLPGrinds()
    {
        $sql = "
            SELECT
            DailySLPGrindID, DailySLPGrindAmount, DailySLPGrindDateAdded
            FROM DailySLPGrind
            LEFT JOIN AssetTeams A on A.AssetTeamID = DailySLPGrind.AxieTeamID
        ";
        $s = $this->db->prepare($sql);
        $s->execute();
        return $s->fetchAll();
    }

    public function getAxieScholar()
    {
        $sql = "SELECT
            P.*
            FROM AssetTeams 
            LEFT JOIN Players P on AssetTeams.PlayerID = P.PlayerID
            WHERE AssetTeamID = :id";
        $s = $this->db->prepare($sql);
        $s->execute(['id' => $this->AssetTeamID]);
        return $s->fetch();
    }
}