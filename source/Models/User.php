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
    // public array|bool $shares = false;
    // public array|bool $AssetTeams = false;

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

    public function __toString(): string
    {
        return $this->getName();
    }

    public function isAdmin(): bool
    {
        $role = Config::ADMIN_TERM;
        return has_inclusion_of($role, array_column($this->getRoles(), 'RoleName'));
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