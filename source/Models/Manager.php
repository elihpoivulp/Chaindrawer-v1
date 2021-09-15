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

    public function getDeposits()
    {
        $sql = "SELECT 
                MS.*,
                CONVERT(MS.ManagerShareID, CHAR) AS ManagerShareID,
                AssetTeamSlug, AssetTeamValue, AssetTeamStatus, AssetTeamName
                FROM ManagerShares MS
                INNER JOIN AssetTeams A on MS.AssetTeamID = A.AssetTeamID
                WHERE ManagerAccountID = :id
                ORDER BY ManagerSharePurchaseDate, AssetTeamStatus
                ";
        $s = $this->db->prepare($sql);
        $s->execute([':id' => $this->getManagerAccountID()]);
        return $s->fetchAll();
    }

    public function getWithdrawals()
    {
        $sql = "SELECT 
                WR.*,
                CONVERT(WR.WithdrawalRequestID, CHAR) AS WithdrawalRequestID,
                WithdrawalAXSinPHP, WithdrawalSLPinPHP, WithdrawalSLPRate, WithdrawalAXSRate
                FROM WithdrawalRequests WR
                LEFT JOIN Withdrawals W on WR.WithdrawalRequestID = W.WithdrawalRequestID
                WHERE ManagerAccountID = :id
                ORDER BY WithdrawalRequestStatus DESC, WithdrawalRequestDate DESC, WithdrawalRequestDateCompleted DESC
                ";
        $s = $this->db->prepare($sql);
        $s->execute([':id' => $this->getManagerAccountID()]);
        return $s->fetchAll();
    }

    public function getPayout(int $payout_id, bool $update_seen = false)
    {
        $sql = "SELECT
                @slp_bal_on_this_day :=(MP.ManagerPayoutTotalSLP + MP.ManagerPayoutLastSLPBalance) AS SLPBalanceOnThisDay,
                @slp_change :=ROUND(((@slp_bal_on_this_day - MP.ManagerPayoutLastSLPBalance) / @slp_bal_on_this_day) * 100, 2) AS SLPChange,
                @axs_bal_on_this_day :=(MP.ManagerPayoutTotalAXS + MP.ManagerPayoutLastAXSBalance) AS AXSBalanceOnThisDay,
                @axs_change :=ROUND(((@axs_bal_on_this_day - MP.ManagerPayoutLastAXSBalance) / @axs_bal_on_this_day) * 100, 2) AS AXSChange,
                AxieTeamPayoutShareRate, AxieTeamPayoutTotalAXS, AxieTeamPayoutTotalSLP,
                AssetTeamName, AssetTeamSlug,
                CONVERT(ManagerPayoutID, CHAR) AS ManagerPayoutID, ManagerPayoutShareRate, ManagerPayoutDate, ManagerPayoutTotalAXS, ManagerPayoutTotalSLP, ManagerPayoutLastAXSBalance, ManagerPayoutLastSLPBalance,
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
                INNER JOIN ManagerAccounts MA on MP.ManagerAccountID = MA.ManagerAccountID
                WHERE MP.ManagerPayoutID = :id 
                GROUP BY ManagerPayoutSeen ASC, ManagerPayoutID DESC";
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
        $res = $s->fetch();
        if ($update_seen) {
            $this->updateSeen($payout_id);
        }
        return $res;
    }

    private function updateSeen($id) {
        $sql = "UPDATE ManagerPayouts SET ManagerPayoutSeen = 1 WHERE ManagerPayoutID = :id";
        $s = $this->db->prepare($sql);
        return $s->execute([':id' => $id]);
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

    public function hasPendingWithdrawal()
    {
        $s = $this->db->prepare('SELECT * FROM WithdrawalRequests WHERE ManagerAccountID = :id AND WithdrawalRequests.WithdrawalRequestStatus = :status ORDER BY WithdrawalRequestID DESC LIMIT 1');
        $s->bindValue(':id', $this->getManagerAccountID());
        $s->bindValue(':status', 'pending');
        $s->execute();
        return $s->fetch();
    }
}