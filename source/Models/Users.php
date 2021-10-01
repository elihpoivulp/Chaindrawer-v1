<?php

namespace CD\Models;

use CD\Core\DB\BaseDBModel;
use Exception;
use PDO;

class Users extends BaseDBModel
{

    public function tableName(): string
    {
        return 'Users';
    }

    public function columns(): array
    {
        return ['*'];
    }

    public function primaryKey(): string
    {
        return 'UserID';
    }

    public function getAllUsers()
    {
        $s = $this->db->prepare("SELECT * FROM Users");
        $s->setFetchMode(PDO::FETCH_CLASS, User::class);
        $s->execute();
        return $s->fetchAll();
    }

    public function isEmailUnique($email): bool
    {
        $sql = "SELECT UserEmail FROM Users WHERE UserEmail = :email LIMIT 1";
        $s = $this->db->prepare($sql);
        $s->bindValue(':email', $email);
        $s->execute();
        return $s->rowCount() ? 0 : 1;
    }

    /**
     * @throws Exception
     */
    public function saveNewUser($data): bool
    {
        try {
            $this->db->beginTransaction();
            if (!has_inclusion_of(':UserMiddleName', $data)) {
                $data[':UserMiddleName'] = null;
            }
            $cols = '';
            foreach (array_keys($data) as $index) {
                $cols .= str_replace(':', '', $index) . ', ';
            }
            $cols = trim($cols, ', ');
            $keys = join(', ', array_keys($data));
            $sql = "INSERT INTO Users ($cols) VALUES ($keys)";
            $s = $this->db->prepare($sql);
            $username = '';
            foreach ($data as $field => $value) {
                $value = trim($value);
                if ($field === ':UserEmail') {
                    $username = explode('@', $value)[0];
                }
                $s->bindValue($field, $value);
            }
            $s->execute();

            $last_id = $this->db->lastInsertId();
            $date = date('Y-m-d H:i:s');
            $sql = "INSERT INTO Logins (LoginUsername, LoginEmail, LoginHashedPassword, LoginIsActive, LoginDateAdded, UserID) VALUES (:username, :email, :pass, 1, :date, :uid)";
            $s = $this->db->prepare($sql);
            $s->bindValue(':username', $username);
            $s->bindValue(':email', $data[':UserEmail']);
            $s->bindValue(':pass', password_hash($username, 1));
            $s->bindValue(':date', $date);
            $s->bindValue(':uid', $last_id);
            $s->execute();

            $sql = "INSERT INTO UserRoles (RoleID, UserID) VALUES (:role, :uid)";
            $s = $this->db->prepare($sql);
            $s->bindValue(':role', 2);
            $s->bindValue(':uid', $last_id);
            $s->execute();

            $sql = "INSERT INTO ManagerAccounts (UserID) VALUES (:uid)";
            $s = $this->db->prepare($sql);
            $s->bindValue(':uid', $last_id);
            $s->execute();

            $this->db->commit();
            return true;

        } catch (Exception | \PDOException $e) {
            $this->db->rollBack();
            throw new Exception($e->getMessage(), 500);
        }
    }
}