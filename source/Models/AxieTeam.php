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
                DailySLPGrindID, SUM(DailySLPGrindAmount) AS DailySLPGrindAmount, DailySLPGrindDateAdded
            FROM DailySLPGrind
            LEFT JOIN AssetTeams A on A.AssetTeamID = DailySLPGrind.AxieTeamID
            WHERE AxieTeamID = :id AND 
                 DailySLPGrindDateAdded BETWEEN DATE_SUB(DATE(NOW()), INTERVAL DAYOFWEEK(NOW())-2 DAY) AND
                 DATE_SUB(DATE(NOW()), INTERVAL DAYOFWEEK(NOW())-8 DAY)
            GROUP BY DATE(DailySLPGrindDateAdded)
            ORDER BY DailySLPGrindDateAdded
        ";
        $s = $this->db->prepare($sql);
        $s->execute([':id' => $this->AssetTeamID]);
        return $s->fetchAll();
    }

    public function getRoyaltyOfCurrentBalance()
    {
        $sql = "SELECT
                (AxieTeams.AxieTeamCurrentAXSBalance * (TT.TeamProfitShare / 100)) AS AXSRoyalty,
                (AxieTeams.AxieTeamCurrentSLPBalance * (TT.TeamProfitShare / 100)) AS SLPRoyalty
                FROM AxieTeams
                INNER JOIN AssetTeams A on A.AssetTeamID = AxieTeams.AssetTeamID
                INNER JOIN TeamTypes TT on A.TeamTypeID = TT.TeamTypeID
                WHERE AxieTeams.AssetTeamID = :id
                ";
        $s = $this->db->prepare($sql);
        $s->execute([':id' => $this->AssetTeamID]);
        return $s->fetch();
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