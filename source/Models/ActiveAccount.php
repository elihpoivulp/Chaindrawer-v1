<?php

namespace CD\Models;

use CD\Core\DB\DB;
use PDO;

class ActiveAccount extends DB
{
    private PDO $db;

    public function __construct()
    {
        $this->db = $this->db();
    }

    public function getRelatedUser(string $token): UserModel|bool
    {
        $sql = 'SELECT
                U.UserID,
                U.UserFirstName,
                U.UserMiddleName,
                U.UserLastName,
                U.UserGender,
                U.UserAddress1,
                U.UserAddress2,
                U.UserPhoto,
                U.UserEmail,
                U.UserDateAdded,
                U.UserDateLastModified,
                AL.ActiveLoginIPAddress,
                AL.ActiveLoginBrowserUserAgent,
                AL.ActiveLoginDateLoggedIn,
                L.LoginUsername
                FROM ActiveLogins AL 
                JOIN Logins L on L.LoginID = AL.LoginID 
                JOIN Users U on U.UserID = L.UserID 
                WHERE ActiveLoginToken = :token';
        $s = $this->db->prepare($sql);
        $s->bindValue(':token', $token, PDO::PARAM_STR);
        $s->execute();
        $s->setFetchMode(PDO::FETCH_CLASS, UserModel::class);
        return $s->fetch();
    }

    public function getLoginData(string $token)
    {
        $s = $this->db->prepare('SELECT * FROM ActiveLogins WHERE ActiveLoginToken = :token');
        $s->bindValue(':token', $token, PDO::PARAM_STR);
        $s->execute();
        return $s->fetch(PDO::FETCH_ASSOC);
    }
}