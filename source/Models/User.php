<?php


namespace CD\Models;


use CD\Config\Config;
use CD\Core\DB\BaseDBModel;
use CD\Core\DuplicateEntry;
use Exception;
use PDO;

class User extends BaseDBModel
{
    use Person;

    public string $LoginHashedPassword;
    public string $LoginUsername;
    public string $LoginEmail;

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
        $sql = "INSERT INTO Users (UserFirstName, UserMiddleName, UserLastName, UserEmail, UserPhone, UserAddress) VALUES (?, ?, ?, ?, ?, ?)";
        $s = $this->db->prepare($sql);
        return $s->execute([
            $this->UserFirstName,
            $this->UserMiddleName,
            $this->UserLastName,
            $this->UserEmail,
            $this->UserPhone,
            $this->UserAddress
        ]);
    }

    public function unsetProperties()
    {
        $this->UserFirstName = '';
        $this->UserMiddleName = null;
        $this->UserLastName = '';
        $this->UserEmail = '';
        $this->UserPhone = '';
        $this->UserAddress = '';
    }

    private function getManagerAccount()
    {
        $sql = "SELECT
                CONVERT(M.ManagerAccountID, CHAR) AS ManagerAccountID,
                M.ManagerTotalAsset,
                M.ManagerAccountCurrentSLPBalance,
                M.ManagerAccountCurrentAXSBalance,
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

    public function updatePassword(string $new_password): bool
    {
        $sql = "UPDATE Logins SET LoginHashedPassword = :new_password WHERE UserID = :id";
        $s = $this->db->prepare($sql);
        $result = $s->execute([
            ':new_password' => $new_password,
            ':id' => $this->getUserID()
        ]);
        if ($result) {
            $this->LoginHashedPassword = $new_password;
        }
        return $result;
    }

    public function updateUsername(string $new_username): bool
    {
        $sql = "UPDATE Logins SET LoginUsername = :new_username WHERE UserID = :id";
        $s = $this->db->prepare($sql);
        $result = $s->execute([
            ':new_username' => $new_username,
            ':id' => $this->getUserID()
        ]);
        if ($result) {
            $this->LoginUsername = $new_username;
        }
        return $result;
    }

    public function updateEmail(string $new_email): bool
    {
        $sql = "UPDATE Logins SET LoginEmail = :new_email WHERE UserID = :id";
        $s = $this->db->prepare($sql);
        $result = $s->execute([
            ':new_email' => $new_email,
            ':id' => $this->getUserID()
        ]);
        if ($result) {
            $this->LoginEmail = $new_email;
        }
        return $result;
    }
}