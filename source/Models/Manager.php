<?php

namespace CD\Models;

use PDO;

class Manager extends AccountModel
{
    // use Person;
    protected int $ManagerAccountID;
    protected float $ManagerTotalAsset;
    protected ?string $ManagerAccountDateAdded = null;
    protected string $ManagerAccountDateLastModified;

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

    public function getManagerDateAdded(): ?string
    {
        return $this->ManagerAccountDateAdded;
    }

    public function getManagerDateModified(): string
    {
        return $this->ManagerAccountDateLastModified;
    }

    public function getManagerAccountID(): int
    {
        return $this->ManagerAccountID;
    }

    public function getFormattedAsset(): string
    {
        return number_format($this->getAsset(), 2);
    }

    public function getShortFormattedAsset(): string
    {
        return toShortFormat($this->getAsset());
    }

    public function getAsset(): float
    {
        return $this->ManagerTotalAsset;
    }

    public function getPayouts(): array
    {
        $year = date('Y');
        $sql = "SELECT * FROM ManagerPayouts
                INNER JOIN ManagerAccounts MA on ManagerPayouts.ManagerAccountID = MA.ManagerAccountID
                WHERE MA.ManagerAccountID = :id AND YEAR(ManagerPayoutDateReceived) = :year
                GROUP BY MONTH(ManagerPayoutDateReceived)";
        $s = $this->db->prepare($sql);
        $s->execute([':id' => $this->ManagerAccountID, ':year' => $year]);
        return $s->fetchAll();
    }

    public function getTeamsAndPayouts(): array
    {
        // TODO: fix
        $sql = "SELECT
                A.AssetTeamName,
                MS.ManagerShareAmount,
                SUM(MP.ManagerPayoutAmount) AS TotalReturned
                FROM ManagerShares MS
                JOIN AssetTeams A on MS.AssetTeamID = A.AssetTeamID
                JOIN ManagerPayouts MP on A.AssetTeamID = MP.SharesForAssetTeamID
                WHERE MS.ManagerAccountID = :id
                GROUP BY A.AssetTeamID ASC
                ";
        $s = $this->db->prepare($sql);
        $s->execute([':id' => $this->ManagerAccountID]);
        return $s->fetchAll();
    }

    // public function getShares(): array
    // {
    //     $sql = "SELECT ManagerShareID, ManagerShareAmount, ManagerSharePurchaseDate, ManagerShareDateLastModified FROM ManagerShares
    //             WHERE ManagerAccountID = :id";
    //     $s = $this->db->prepare($sql);
    //     $s->execute([':id' => $this->ManagerAccountID]);
    //     return $s->fetchAll(PDO::FETCH_CLASS, ManagerSharesModel::class);
    // }

    public function getTotalIncome(): string
    {
        $sql = "SELECT SUM(ManagerPayoutAmount) AS TotalIncome FROM ManagerPayouts
                WHERE ManagerAccountID = :id";
        $s = $this->db->prepare($sql);
        $s->execute([':id' => $this->ManagerAccountID]);
        return toShortFormat($s->fetchAll()[0]['TotalIncome']);
    }

    public function getMyTeams(): array
    {
        $sql = "SELECT
                A.*,
                TT.TeamProfitShare,
                ROUND((MS.ManagerShareAmount / A.AssetTeamValue) * 100, 2) AS OwnershipRate,
                AP.AssetPlatformName, AP.AssetPlatformWebsiteLink
                FROM ManagerShares MS
                INNER JOIN AssetTeams A on MS.AssetTeamID = A.AssetTeamID
                INNER JOIN TeamTypes TT on A.TeamTypeID = TT.TeamTypeID
                INNER JOIN AssetPlatforms AP on A.AssetPlatformID = AP.AssetPlatformID
                WHERE MS.ManagerAccountID = :id
                GROUP BY A.AssetTeamID ASC
                ";
        $s = $this->db->prepare($sql);
        $s->setFetchMode(PDO::FETCH_CLASS, Team::class);
        $s->execute([':id' => $this->ManagerAccountID]);
        return $s->fetchAll();
    }
}