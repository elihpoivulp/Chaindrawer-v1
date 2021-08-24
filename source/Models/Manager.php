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

    public function getManagerDateAdded()
    {
        return $this->ManagerAccountDateAdded;
    }

    public function getManagerDateModified(): string
    {
        return $this->ManagerAccountDateLastModified;
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
        $sql = "SELECT
                A.AssetTeamName,
                MS.ManagerShareAmount,
                SUM(MP.ManagerPayoutAmount) AS TotalReturned
                FROM ManagerShares MS
                JOIN SharesForAssetTeams SFAT on SFAT.SharesForAssetTeamID = MS.FundForAssetTeamID
                JOIN AssetTeams A on SFAT.SharesForAssetTeamID = A.ShareForAssetTeamID
                JOIN ManagerPayouts MP on SFAT.SharesForAssetTeamID = MP.SharesForAssetTeamID
                WHERE MS.ManagerAccountID = :id
                GROUP BY SFAT.SharesForAssetTeamID ASC
                ";
        $s = $this->db->prepare($sql);
        $s->execute([':id' => $this->ManagerAccountID]);
        return $s->fetchAll();
    }

    public function getShares(): array
    {
        $sql = "SELECT ManagerShareID, ManagerShareAmount, ManagerSharePurchaseDate, ManagerShareDateLastModified FROM ManagerShares
                WHERE ManagerAccountID = :id";
        $s = $this->db->prepare($sql);
        $s->execute([':id' => $this->ManagerAccountID]);
        return $s->fetchAll(PDO::FETCH_CLASS, ManagerSharesModel::class);
    }

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
                A.AssetTeamID,
                A.AssetTeamName
                FROM ManagerShares MS
                JOIN SharesForAssetTeams SFAT on SFAT.SharesForAssetTeamID = MS.FundForAssetTeamID
                JOIN AssetTeams A on SFAT.SharesForAssetTeamID = A.ShareForAssetTeamID
                WHERE MS.ManagerAccountID = :id
                GROUP BY SFAT.SharesForAssetTeamID ASC
                ";
        $s = $this->db->prepare($sql);
        $s->setFetchMode(PDO::FETCH_CLASS, Team::class);
        $s->execute([':id' => $this->ManagerAccountID]);
        return $s->fetchAll();
    }
}