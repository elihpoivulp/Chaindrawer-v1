<?php

namespace CD\Models;

use CD\Core\DB\BaseDBModel;
use PDO;

class ManagerSharesModel extends BaseDBModel
{
    // public int $SharesForAsset;
    // public int $ShareForRelatedAvailableShares;
    // public float $ShareCapital;
    // public string $ShareDateApplied;
    // public ?string $ShareDateUpdated;
    // public int $ShareAppliedByUserID;
    // public ?int $ShareUpdatedByUserID;
    protected int $ManagerShareID;
    protected float $ManagerShareAmount;
    protected string $ManagerSharePurchaseDate;
    protected string $ManagerShareDateLastModified;

    public ?array $transactions;

    public function __construct()
    {
        parent::__construct();
        $this->transactions = $this->getTransactions() ?? null;
    }

    public function getTransactions(): array
    {
        // $sql = 'SELECT * FROM ShareTransactionsLog WHERE ShareTransactionLogID = :id';
        // $s = $this->db->prepare($sql);
        // $s->execute([':id' => $this->ShareID]);
        // return $s->fetchAll(PDO::FETCH_CLASS, ShareTransactionsLogModel::class);
        return [];
    }
}