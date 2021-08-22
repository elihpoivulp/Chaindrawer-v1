<?php

namespace CD\Models;

use CD\Core\DB\BaseDBModel;

class AccountModel extends BaseDBModel
{
    protected int $AccountID;
    protected string $AccountBalance;
    protected string $AccountDateOpened;
    protected int $AccountIsActive;
    protected string $AccountDateLastModified;
    protected ?string $AccountDateClosed;

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

    public function getStatus(): int
    {
        return $this->AccountIsActive;
    }

    public function getDateModified(): string
    {
        return $this->AccountDateLastModified;
    }

    public function getDateOpened(): string
    {
        return $this->AccountDateOpened;
    }

    public function getDateClosed(): string
    {
        return $this->AccountDateClosed;
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