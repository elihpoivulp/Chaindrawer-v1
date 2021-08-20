<?php

namespace CD\Models;

use CD\Core\DB\BaseDBModel;

class AssetPlatformModel extends BaseDBModel
{

    public function tableName(): string
    {
        return 'AssetPlatforms';
    }

    public function columns(): array
    {
        return ['*'];
    }

    public function primaryKey(): string
    {
        return 'AssetPlatformID';
    }
}