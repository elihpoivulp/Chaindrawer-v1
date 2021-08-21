<?php


namespace CD\Core\Models;

use CD\Core\DB\BaseDBModel;

abstract class DBFormModel extends BaseDBModel
{
    public function setPropertyValues(array $data): void
    {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }
}