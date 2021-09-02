<?php

namespace CD\Models;

use PDO;

class Manager extends AccountModel
{
    // use Person;
    protected string $ManagerAccountID;
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

    public function getManagerAccountID(): string
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
        $sql = "SELECT *, CONVERT(ManagerPayoutID, CHAR) AS ManagerPayoutID FROM ManagerPayouts
                INNER JOIN ManagerAccounts MA on ManagerPayouts.ManagerAccountID = MA.ManagerAccountID
                WHERE MA.ManagerAccountID = :id AND YEAR(ManagerPayoutDate) = :year
                ORDER BY ManagerPayoutSeen, ManagerPayoutDate DESC 
                ";
        $s = $this->db->prepare($sql);
        $s->execute([':id' => $this->ManagerAccountID, ':year' => $year]);
        return $s->fetchAll();
    }

    public function getPayout(int $payout_id): array
    {
        $sql = "SELECT
                AxieTeamPayoutShareRate, AxieTeamPayoutTotalAXS, AxieTeamPayoutTotalSLP,
                AssetTeamName, AssetTeamSlug,
                CONVERT(ManagerPayoutID, CHAR) AS ManagerPayoutID, ManagerPayoutShareRate, ManagerPayoutDate, ManagerPayoutTotalAXS, ManagerPayoutTotalSLP,
                FortnightAxieWithdrawalTotalAXS, FortnightAxieWithdrawalTotalSLP,
                AxieScholarPayoutShareRate, AxieScholarPayoutTotalAXS, AxieScholarPayoutTotalSLP,
                ChainDrawerAxiePayoutShareRate, ChainDrawerAxiePayoutTotalAXS, ChainDrawerAxiePayoutTotalSLP
                FROM
                ManagerPayouts MP
                INNER JOIN AxieTeamPayouts ATP on MP.AxieTeamPayoutID = ATP.AxieTeamPayoutID
                INNER JOIN AxieTeams A on ATP.AxieTeamID = A.AssetTeamID
                INNER JOIN AssetTeams T on A.AssetTeamID = T.AssetTeamID
                INNER JOIN FortnightAxieWithdrawals FAW on ATP.FortnightAxieWithdrawalID = FAW.FortnightAxieWithdrawalID
                INNER JOIN AxieScholarPayouts ASP on ATP.AxieTeamPayoutID = ASP.AxieTeamPayoutID
                INNER JOIN ChainDrawerAxiePayouts CDAP on ATP.AxieTeamPayoutID = CDAP.AxieTeamPayoutID
                WHERE MP.ManagerPayoutID = :id GROUP BY ManagerPayoutSeen ASC, ManagerPayoutID DESC";
        // $year = date('Y');
        // $sql = "SELECT * FROM ManagerPayouts
        //         INNER JOIN ManagerAccounts MA on ManagerPayouts.ManagerAccountID = MA.ManagerAccountID
        //         WHERE MA.ManagerAccountID = :id AND YEAR(ManagerPayoutDate) = :year
        //         GROUP BY MONTH(ManagerPayoutDate)
        //         ORDER BY ManagerPayoutSeen, ManagerPayoutDate DESC
        //         ";
        $s = $this->db->prepare($sql);
        $s->bindValue(':id', $payout_id, PDO::PARAM_INT);
        $s->execute();
        return $s->fetch();
    }

    public function getReturnSummary(): array
    {
        // this sql is slow
        // $sql = "SELECT
        //         @tr :=SUM(IFNULL(MP.ManagerPayoutAmount, 0)) AS TotalReturned,
        //         A.AssetTeamName,
        //         MS.ManagerShareAmount,
        //         IF (@tr <= MS.ManagerShareAmount, FLOOR((@tr / MS.ManagerShareAmount) * 100), 100) AS ReturnRate
        //         FROM ManagerShares MS
        //         INNER JOIN AssetTeams A on MS.AssetTeamID = A.AssetTeamID
        //         LEFT JOIN ManagerPayouts MP on A.AssetTeamID = MP.SharesForAssetTeamID
        //         WHERE MS.ManagerAccountID = :id
        //         GROUP BY A.AssetTeamID ASC
        //         ";
        $sql = "SELECT
                A.AssetTeamName,
                MS.ManagerShareAmount,
                SUM(MP.ManagerPayoutTotalPHP) AS TotalReturned
                FROM ManagerShares MS
                INNER JOIN AssetTeams A on MS.AssetTeamID = A.AssetTeamID
                LEFT JOIN ManagerPayouts MP on MS.ManagerAccountID = MP.ManagerAccountID
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
        $sql = "SELECT SUM(ManagerPayoutTotalPHP) AS TotalIncome FROM ManagerPayouts
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