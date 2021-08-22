<?php

namespace CD\Models;

use CD\Core\DB\BaseDBModel;
use PDO;

class Team extends BaseDBModel
{
    public ?int $AssetTeamID = null;
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

    public function saveNewTeam(): bool
    {
        $sql = 'INSERT INTO AssetTeams (AssetTeamName, AssetTeamSlug, AssetPlatformID, TeamTypeID, PlayerID, ShareForAssetTeamID) VALUES (:name, :slug, :platform_id, :team_type_id, :player_id, :fund_id)';
        $s = $this->db->prepare($sql);
        $s->bindValue(':name', $this->AssetTeamName);
        $s->bindValue(':slug', slugify($this->AssetTeamName));
        $s->bindValue(':platform_id', $this->AssetPlatFormID, PDO::PARAM_INT);
        $s->bindValue(':team_type_id', $this->TeamTypeID, PDO::PARAM_INT);
        $s->bindValue(':player_id', $this->PlayerID, PDO::PARAM_INT);
        $s->bindValue(':fund_id', $this->ShareForAssetTeamID, PDO::PARAM_INT);
        return $s->execute();
    }

    public function insertAmount()
    {
        $sql = "INSERT INTO SharesForAssetTeams (SharesForAssetTeamGoalAmount) VALUES (:amt)";
        $s = $this->db->prepare($sql);
        if ($s->execute(['amt' => str_replace(',', '', $this->Amount)])) {
            return $this->db->lastInsertId();
        }
        return false;
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