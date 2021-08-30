<?php

namespace CD\Models;

use CD\Core\DB\BaseDBModel;
use PDO;

class AxiePayouts extends BaseDBModel
{
    public function tableName(): string
    {
        return 'AxieTeamPayouts';
    }

    public function columns(): array
    {
        return ['*'];
    }

    public function primaryKey(): string
    {
        return 'AxieTeamPayoutID';
    }
}