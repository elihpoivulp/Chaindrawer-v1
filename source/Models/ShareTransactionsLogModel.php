<?php

namespace CD\Models;

use CD\Core\DB\BaseDBModel;

class ShareTransactionsLogModel
{
    protected int  $ShareTransactionLogID;
    protected int $ShareTransactionLogRelatedShareID;
    protected int $ShareTransactionLogRelatedPaymentMethod;
    protected string $ShareTransactionLogDate;

    public function getShareDetails()
    {
        // return share transaction details
        return;
    }
}