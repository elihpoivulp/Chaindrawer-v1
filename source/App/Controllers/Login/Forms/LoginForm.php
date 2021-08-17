<?php


namespace CD\App\Controllers\Login\Forms;


use CD\Core\Forms\ModelForm;
use CD\Core\Sessions\SessionsUserAuth;

class LoginForm extends ModelForm
{
    // implemented from abstract validation
    public function rules(): array
    {
        return [
            'username' => [self::RULE_REQUIRED],
            'password' => [self::RULE_REQUIRED,
                [
                    self::RULE_MIN, 'min' => 8
                ],
                [
                    self::RULE_MAX, 'max' => 60
                ]
            ]
        ];
    }

    public function validate()
    {
        $result = parent::validate();
        if ($result) {
            $user = $this->model->authenticate();
            if ($user) {
                $auth = new SessionsUserAuth();
                return $auth->login($user);
            } else {
                $this->model->password = '';
                return false;
            }
        }
        return $result;
    }
}