<?php

namespace CD\Models;

use CD\Core\Models\DBFormModel;
use CD\Core\Sessions\Session;
use Exception;
use PDO;

class AuthenticationModel extends DBFormModel
{
    // static public ?string $table_name = 'AuthenticationModel';

    public string $username = '';
    public string $password = '';


    public function tableName(): string
    {
        return 'Logins';
    }

    public function primaryKey(): string
    {
        return 'LoginUsername';
    }

    public function columns(): array
    {
        return ['*'];
    }

    /**
     * @throws Exception
     */
    public function authenticate()
    {
        $login = $this->select($this->columns())->where([$this->primaryKey() => $this->username])->fetch(['mode' => [PDO::FETCH_CLASS, Login::class]]);
        if (!$login || $login->LoginIsActive == 0) {
            return false;
        }
        if (password_verify($this->password, $login->LoginHashedPassword)) {
            return $login;
        } else {
            return false;
        }
    }
}