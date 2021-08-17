<?php


namespace CD\App\Controllers\Auth\Forms;

use CD\Core\Forms\ModelForm;
use CD\Core\Request;
use CD\Core\Sessions\SessionsUserAuth;
use CD\Core\Utils\Token;
use PDO;

class AuthForm extends ModelForm
{
    // implemented from abstract validation
    public function rules(): array
    {
        return [
            'username' => [self::RULE_REQUIRED],
            'password' => [self::RULE_REQUIRED,
                [
                    // TODO: change minimum chars
                    self::RULE_MIN, 'min' => 4
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
                if (!SessionsUserAuth::isLoggedIn()) {
                    $token = new Token();
                    $token = $token->getHash();
                    $ip = Request::getIPAddress();
                    $user_agent = Request::getUserAgent();
                    $s = $this->model->db()->prepare("INSERT INTO ActiveLogins (ActiveLoginToken, ActiveLoginIPAddress, ActiveLoginBrowserUserAgent, LoginID) VALUES (:token, :ip, :user_agent, :id)");
                    $s->bindParam('token', $token);
                    $s->bindParam('id', $user->LoginID, PDO::PARAM_INT);
                    $s->bindParam('ip', $ip);
                    $s->bindParam('user_agent', $user_agent);
                    $s->execute();
                    return SessionsUserAuth::login($token);
                }
                // return true;
            } else {
                $this->model->password = '';
                return false;
            }
        }
        $this->model->password = '';
        return $result;
    }
}