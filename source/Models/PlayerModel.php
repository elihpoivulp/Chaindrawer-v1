<?php

namespace CD\Models;

use CD\Core\DB\BaseDBModel;

class PlayerModel extends BaseDBModel
{
    public function tableName(): string
    {
        return 'Players';
    }

    public function columns(): array
    {
        return ['*'];
    }

    public function primaryKey(): string
    {
        return 'PlayerID';
    }
}