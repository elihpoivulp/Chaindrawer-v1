<?php

namespace CD\App\Controllers\Admin\Forms;

use CD\Core\Forms\ModelForm;

class NewUserForm extends ModelForm
{
    // implemented from abstract validation
    public function rules(): array
    {
        return [
            'UserFirstName' => [self::RULE_REQUIRED,
                [
                    self::RULE_MIN, 'min' => 4
                ],
                [
                    self::RULE_MAX, 'max' => 60
                ]
            ],
            'UserLastName' => [self::RULE_REQUIRED,
                [
                    self::RULE_MIN, 'min' => 4
                ],
                [
                    self::RULE_MAX, 'max' => 60
                ]
            ],
            'UserGender' => [self::RULE_REQUIRED,
                [
                    self::RULE_MIN, 'min' => 1
                ],
                [
                    self::RULE_MAX, 'max' => 1
                ]
            ],
            'UserPhone' => [self::RULE_REQUIRED,
                [
                    self::RULE_MIN, 'min' => 11
                ],
                [
                    self::RULE_MAX, 'max' => 11
                ]
            ],
            'UserEmail' => [self::RULE_REQUIRED, self::RULE_EMAIL,
                [
                    self::RULE_MIN, 'min' => 8
                ],
                [
                    self::RULE_MAX, 'max' => 100
                ]
            ],
            'UserAddress1' => [self::RULE_REQUIRED,
                [
                    self::RULE_MAX, 'max' => 255
                ]
            ],
            'UserAddress2' => [],
            'UserMiddleName' => [],
        ];
    }

    public function validate()
    {
        $result = parent::validate();
        if ($result) {
            if ($save = $this->model->save()) {
                $this->model->unsetProperties();
            }
            return $save;
        }
        return $result;
    }
}