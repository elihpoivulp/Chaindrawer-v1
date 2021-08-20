<?php

namespace CD\Models;

use CD\Core\Models\DBFormModel;
use PDO;

class TeamModel extends DBFormModel
{

    public string $AssetTeamName = '';
    public ?int $AssetPlatFormID = null;
    public ?int $TeamTypeID = null;
    public ?int $PlayerID = null;
    public ?int $ShareForAssetTeamID = null;
    public string $Amount = '';


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

    public function getTeamTypes(): array
    {
        $s = $this->db->prepare('SELECT * FROM TeamTypes');
        $s->execute();
        return $s->fetchAll();
    }

    public function insertNewAmount($amount)
    {
        $sql = "INSERT INTO SharesForAssetTeams (SharesForAssetTeamGoalAmount) VALUES (:amt)";
        $s = $this->db->prepare($sql);
        if ($s->execute([':amt' => $amount])) {
            return $this->db->lastInsertId();
        }
        return false;
    }

    public function saveNewTeam(): bool
    {
        $sql = 'INSERT INTO AssetTeams (AssetTeamName, AssetPlatformID, TeamTypeID, PlayerID, ShareForAssetTeamID) VALUES (:name, :platform_id, :team_type_id, :player_id, :fund_id)';
        $s = $this->db->prepare($sql);
        $s->bindValue(':name', $this->AssetTeamName);
        $s->bindValue(':platform_id', $this->AssetPlatFormID, PDO::PARAM_INT);
        $s->bindValue(':team_type_id', $this->TeamTypeID, PDO::PARAM_INT);
        $s->bindValue(':player_id', $this->PlayerID, PDO::PARAM_INT);
        $s->bindValue(':fund_id', $this->ShareForAssetTeamID, PDO::PARAM_INT);
        return $s->execute();

    }

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