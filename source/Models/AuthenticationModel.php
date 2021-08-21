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

        $s = $this->db->prepare('SELECT * FROM Logins WHERE LoginUsername = :username');
        $s->execute(['username' => $this->username]);
        $s->setFetchMode(PDO::FETCH_CLASS, Login::class);
        $login = $s->fetch();
        // $login = $this->select($this->columns())->where([$this->primaryKey() => $this->username])->fetch(['mode' => []]);
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