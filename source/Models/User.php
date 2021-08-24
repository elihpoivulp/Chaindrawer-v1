<?php


namespace CD\Models;


use CD\Config\Config;
use CD\Core\DB\BaseDBModel;
use PDO;

class User extends BaseDBModel
{
    use Person;

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

    public $manager = false;

    public function __construct()
    {
        parent::__construct();
        if ($this->isManager()) {
            $this->manager = $this->getManagerAccount();
        }
    }

    public function getRoles(): array
    {
        $s = $this->db->prepare(
            'SELECT R.RoleName FROM UserRoles UR JOIN Roles R on R.RoleID = UR.RoleID WHERE UR.UserID = :id'
        );
        $s->bindValue(':id', $this->UserID, PDO::PARAM_INT);
        $s->execute();
        return $s->fetchAll();
    }

    public function getLogin()
    {
        $s = $this->db->prepare("SELECT * FROM Logins WHERE UserID = ?");
        $s->execute([$this->UserID]);
        return $s->fetch();
    }

    public function __toString(): string
    {
        return $this->getUserName();
    }

    public function isAdmin(): bool
    {
        $role = Config::ADMIN_TERM;
        return has_inclusion_of($role, array_column($this->getRoles(), 'RoleName'));
    }

    public function save(): bool
    {
        $sql = "INSERT INTO Users (UserFirstName, UserMiddleName, UserLastName, UserEmail, UserPhone, UserAddress1, UserAddress2) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $s = $this->db->prepare($sql);
        return $s->execute([
            $this->UserFirstName,
            $this->UserMiddleName,
            $this->UserLastName,
            $this->UserEmail,
            $this->UserPhone,
            $this->UserAddress1,
            $this->UserAddress2,
        ]);
    }

    public function unsetProperties()
    {
        $this->UserFirstName = '';
        $this->UserMiddleName = null;
        $this->UserLastName = '';
        $this->UserEmail = '';
        $this->UserPhone = '';
        $this->UserAddress1 = '';
        $this->UserAddress2 = null;
    }

    private function getManagerAccount()
    {
        $sql = "SELECT
                M.ManagerAccountID,
                M.ManagerTotalAsset,
                A.AccountID,
                A.AccountBalance,
                A.AccountDateOpened,
                A.AccountDateLastModified
                FROM ManagerAccounts M 
                JOIN Accounts A on A.AccountID = M.AccountID
                WHERE M.UserID = :id";
        $s = $this->db->prepare($sql);
        $s->bindValue(':id', $this->UserID, PDO::PARAM_INT);
        $s->setFetchMode(PDO::FETCH_CLASS, Manager::class);
        $s->execute();
        return $s->fetch();
    }

    private function isManager(): bool
    {
        if (!empty($this->UserID)) {
            $role = Config::MANAGER_TERM;
            return has_inclusion_of($role, array_column($this->getRoles(), 'RoleName'));
        }
        return false;
    }
}