<?php

namespace CD\Models;

use CD\Core\DB\BaseDBModel;
use PDO;

class ManagerModel extends AccountModel
{
    public int $ManagerAccountID;
    protected int $ManagerAccountRelatedUserID;
    protected int $ManagerAccountRelatedAccountID;
    protected int $ManagerAccountAddedByUserID;
    protected ?int $ManagerAccountUpdatedByUserID;
    protected float $ManagerTotalAsset;

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
}