<?php


namespace CD\Core\Models;


use CD\Core\Forms\FormValidations;

abstract class BaseModel extends FormValidations
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