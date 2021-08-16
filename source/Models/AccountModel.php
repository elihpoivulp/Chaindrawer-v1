<?php

namespace CD\Models;

use CD\Core\DB\BaseDBModel;

class AccountModel extends BaseDBModel
{
    protected int $AccountID;
    protected string $AccountBalance;
    protected string $AccountDateOpened;
    protected ?string $AccountDateUpdated;
    protected int $AccountOpenedByUserID;
    protected ?int $AccountUpdatedByUserID;

    public function getFormattedBalance(): string
    {
        return number_format($this->getBalance(), 2);
    }

    public function getShortFormatBalance(): string
    {
        return toShortFormat($this->getBalance());
    }

    public function getBalance(): string
    {
        return $this->AccountBalance;
    }
}