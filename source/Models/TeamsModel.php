<?php

namespace CD\Models;

use CD\Core\DB\BaseDBModel;
use PDO;

class TeamsModel extends BaseDBModel
{
    public int $AssetTeamID;
    public string $TeamName;

    public function getInfo()
    {
        $s = $this->db->prepare(
            'SELECT * FROM TeamInfo WHERE TeamID = :id'
        );
        $s->execute([':id' => $this->AssetTeamID]);
        return $s->fetch();
    }

    public function getTeamManagers()
    {
        $s = $this->db->prepare(
            '
                SELECT 
                MS.ManagerAccountID,
                ROUND((MS.ManagerShareAmount / SFAT.SharesForAssetTeamGoalAmount) * 100, 2) AS OwnershipRate
                FROM ManagerShares MS
                JOIN SharesForAssetTeams SFAT ON SFAT.SharesForAssetTeamID = MS.FundForAssetTeamID 
                WHERE SharesForAssetTeamID = :id'
        );
        $s->execute([':id' => $this->AssetTeamID]);
        return $s->fetchAll();
    }
}