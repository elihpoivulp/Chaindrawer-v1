<?php

namespace CD\Models;

use CD\Core\Models\DBFormModel;

class AccountModel extends DBFormModel
{
    protected int $AccountID;
    protected string $AccountBalance;
    protected string $AccountDateOpened;
    protected ?string $AccountDateUpdated;
    protected int $AccountOpenedByUserID;
    protected ?int $AccountUpdatedByUserID;


    public function tableName(): string
    {
        return 'Accounts';
    }

    public function columns(): array
    {
        return ['*'];
    }

    public function primaryKey(): string
    {
        return 'AccountID';
    }

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