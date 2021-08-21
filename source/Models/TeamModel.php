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

    public function getTeamBySlug(string $slug): array
    {
        $s = $this->db->prepare('CALL GetTeamBySlug(:slug)');
        $s->execute(['slug' => $slug]);
        return $s->fetch();
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

    // public function getInfo(int $id)
    // {
    //     $s = $this->db->prepare(
    //         'SELECT * FROM TeamInfo WHERE TeamID = :id'
    //     );
    //     $s->execute([':id' => $id]);
    //     return $s->fetch();
    // }

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

    // protected function getSelectSQL(array $where = []): string
    // {
    //     $sql = $this->teams_select_sql;
    //     if (!empty($where)) {
    //         $sql .= ' WHERE ' . $this->conditionArrayToString($where);
    //     }
    //     return $sql . ' GROUP BY AT.AssetTeamID';
    // }
}