<?php

namespace CD\Models;

use CD\Core\DB\BaseDBModel;
use Exception;
use PDO;

class AuthenticationModel extends BaseDBModel
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
        $s = $this->db->prepare('SELECT * FROM Logins WHERE (LoginUsername = ? OR LoginEmail = ?)');
        $s->bindValue(1, $this->username);
        $s->bindValue(2, $this->username);
        $s->execute();
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