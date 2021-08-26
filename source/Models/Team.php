<?php

namespace CD\Models;

use CD\Core\DB\BaseDBModel;
use PDO;

class Team extends BaseDBModel
{
    public int $AssetTeamID;
    public string $AssetTeamName = '';
    public float $AssetTeamValue;
    public float $AssetTeamAmountCollected;
    public int $AssetTeamStatus;
    public string $AssetTeamDateAdded;
    public string $AssetTeamDateLastModified;
    public string $TeamPlatform;
    public string $TeamPlatformWebsite;
    public int $TeamManagerCount;
    public int $TotalManagerShares;
    public string $TeamType;
    public ?int $AssetPlatFormID = null;
    public ?int $TeamTypeID = null;
    public ?int $TeamPlayerID = null;
    public ?int $PlatformID = null;
    public ?string $TeamPlayerIGN = null;
    public ?int $ShareForAssetTeamID = null;


    // non-db fields
    public string $Amount = '';
    public AxieTeam $Axie;

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
        $sql = 'INSERT INTO AssetTeams (AssetTeamName, AssetTeamSlug, AssetPlatformID, TeamTypeID, PlayerID, AssetTeamValue) VALUES (:name, :slug, :platform_id, :team_type_id, :player_id, :value)';
        $s = $this->db->prepare($sql);
        $s->bindValue(':name', $this->AssetTeamName);
        $s->bindValue(':slug', slugify($this->AssetTeamName));
        $s->bindValue(':platform_id', $this->PlatformID, PDO::PARAM_INT);
        $s->bindValue(':team_type_id', $this->TeamTypeID, PDO::PARAM_INT);
        $s->bindValue(':player_id', $this->TeamPlayerID, PDO::PARAM_INT);
        $s->bindValue(':value', $this->Amount);
        return $s->execute();
    }

    public function AxieTeam()
    {
        if ($this->isAxieTeam()) {
            return $this->Axie;
        }
        // TODO: Throw Exception
        exit('This is not an axie team');
    }

    // public function insertAmount()
    // {
    //     $sql = "INSERT INTO SharesForAssetTeams (SharesForAssetTeamGoalAmount) VALUES (:amt)";
    //     $s = $this->db->prepare($sql);
    //     if ($s->execute(['amt' => str_replace(',', '', $this->Amount)])) {
    //         return $this->db->lastInsertId();
    //     }
    //     return false;
    // }

    public function getTeamManagers()
    {
        $s = $this->db->prepare(
            "
                SELECT
                MA.*,
                ROUND((MS.ManagerShareAmount / $this->AssetTeamValue) * 100, 2) AS OwnershipRate
                FROM ManagerShares MS
                JOIN ManagerAccounts MA on MS.ManagerAccountID = MA.ManagerAccountID
                WHERE MS.AssetTeamID = :id"
        );
        $s->setFetchMode(PDO::FETCH_CLASS, Manager::class);
        $s->execute([':id' => $this->AssetTeamID]);
        return $s->fetchAll();
    }

    protected function isAxieTeam(): bool
    {
        $sql = "SELECT * FROM AxieTeams WHERE AssetTeamID = :id";
        $s = $this->db->prepare($sql);
        $s->setFetchMode(PDO::FETCH_CLASS, AxieTeam::class);
        $result = $s->execute([':id' => $this->AssetTeamID]);
        if ($result) {
            $this->Axie = $s->fetch();
        }
        return $result;
    }
}