<?php

namespace CD\App\Controllers\Admin\Forms;

use CD\Core\Forms\ModelForm;

class NewManagerForm extends ModelForm
{
    // implemented from abstract validation
    public function rules(): array
    {
        return [
            // 'AssetTeamName' => [self::RULE_REQUIRED,
            //     [
            //         self::RULE_MIN, 'min' => 8
            //     ],
            //     [
            //         self::RULE_MAX, 'max' => 50
            //     ]
            // ],
            // 'Amount' => [self::RULE_REQUIRED, self::RULE_MONEY,
            //     [
            //         self::RULE_MIN, 'min' => 8
            //     ],
            //     [
            //         self::RULE_MAX, 'max' => 10
            //     ]
            // ],
            // 'PlayerID' => [self::RULE_REQUIRED, self::RULE_NUMERIC],
            // 'AssetPlatFormID' => [self::RULE_REQUIRED, self::RULE_NUMERIC],
            // 'TeamTypeID' => [self::RULE_REQUIRED, self::RULE_NUMERIC]
        ];
    }

    public function validate()
    {
        $result = parent::validate();
        if ($result) {
           return true;
        }
        return $result;
    }
}