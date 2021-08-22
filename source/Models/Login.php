<?php


namespace CD\Models;


use CD\Core\DB\BaseDBModel;
use Exception;
use PDO;

class Login extends BaseDBModel
{
    public int $LoginRelatedUserID;

    public function __construct()
    {
    }

    public function tableName(): string
    {
        return 'Logins';
    }

    public function primaryKey(): string
    {
        return 'LoginID';
    }

    public function columns(): array
    {
        return ['*'];
    }

    /**
     * @throws Exception
     */
    public function getRelatedUser()
    {
        return $this->select($this->columns())->where([$this->primaryKey() => $this->LoginRelatedUserID])->setFetchMode([PDO::FETCH_CLASS, 'User'])->fetch();
    }

    public function getUserRole()
    {
        echo '<pre>';
        print_r($this->getRelatedUser());
        echo '</pre>';
    }
}