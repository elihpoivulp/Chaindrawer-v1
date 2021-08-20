<?php


namespace CD\Models;


use CD\Config\Config;
use CD\Core\DB\BaseDBModel;
use PDO;

class UserModel extends BaseDBModel
{
    protected int $UserID;
    protected string $UserFirstName;
    protected ?string $UserMiddleName;
    protected string $UserLastName;
    protected string $UserGender;
    protected string $UserAddress1;
    protected ?string $UserAddress2;
    protected ?string $UserPhoto;
    protected string $UserEmail;

    public $manager = false;
    // public array|bool $shares = false;
    // public array|bool $AssetTeams = false;

    public function __construct()
    {
        parent::__construct();
        if ($this->isManager()) {
            $this->manager = $this->getManagerAccount();
        }
        // $this->shares = $this->getShares() ?? null;
        // if ($this->shares) {
        //     $this->AssetTeams = $this->getTeams();
        // }
    }

    public function __toString(): string
    {
        return $this->getName();
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

    public function getFirstName(): string
    {
        return $this->UserFirstName;
    }

    public function getLastName(): string
    {
        return $this->UserLastName;
    }

    public function getMiddleName(): string
    {
        return $this->UserMiddleName;
    }

    public function getFullName(): string
    {
        return $this->getFirstName() . ' ' . $this->getMiddleName() . ' ' . $this->getLastName();
    }

    public function getName(): string
    {
        return $this->getFirstName() . ' ' . $this->getLastName();
    }

    public function isAdmin(): bool
    {
        $role = Config::ADMIN_TERM;
        return has_inclusion_of($role, array_column($this->getRoles(), 'RoleName'));
    }

    // public function getShares(): bool
    // {
    //     $sql = 'SELECT * FROM Shares '
    // }

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
        $s->setFetchMode(PDO::FETCH_CLASS, ManagerModel::class);
        $s->execute();
        return $s->fetch();
    }

    public function getShares(): array
    {
        if ($this->manager) {
            $sql = "SELECT ManagerShareID, ManagerShareAmount, ManagerSharePurchaseDate, ManagerShareDateLastModified FROM ManagerShares
                WHERE ManagerAccountID = :id";
            $s = $this->db->prepare($sql);
            $s->execute([':id' => $this->manager->ManagerAccountID]);
            return $s->fetchAll(PDO::FETCH_CLASS, ManagerSharesModel::class);
        }
        return [];
    }

    public function getTotalIncome(): string
    {
        $sql = "SELECT SUM(ManagerPayoutAmount) AS TotalIncome FROM ManagerPayouts
                WHERE ManagerAccountID = :id";
        $s = $this->db->prepare($sql);
        $s->execute([':id' => $this->manager->ManagerAccountID]);
        return toShortFormat($s->fetchAll()[0]['TotalIncome']);
    }

    public function getTeamsAndPayouts(): array
    {
        if ($this->manager) {
            $sql = "SELECT
                A.AssetTeamName,
                MS.ManagerShareAmount,
                SUM(MP.ManagerPayoutAmount) AS TotalReturned
                FROM ManagerShares MS
                JOIN SharesForAssetTeams SFAT on SFAT.SharesForAssetTeamID = MS.FundForAssetTeamID
                JOIN AssetTeams A on SFAT.SharesForAssetTeamID = A.FundForAssetTeamID
                JOIN ManagerPayouts MP on SFAT.SharesForAssetTeamID = MP.SharesForAssetTeamID
                WHERE MS.ManagerAccountID = :id
                GROUP BY SFAT.SharesForAssetTeamID ASC
                ";
            $s = $this->db->prepare($sql);
            $s->execute([':id' => $this->manager->ManagerAccountID]);
            return $s->fetchAll();
        }
        return [];
    }

    public function getMyTeams(): array
    {
        if ($this->manager) {
            $sql = "SELECT
                A.AssetTeamID,
                A.AssetTeamName
                FROM ManagerShares MS
                JOIN SharesForAssetTeams SFAT on SFAT.SharesForAssetTeamID = MS.FundForAssetTeamID
                JOIN AssetTeams A on SFAT.SharesForAssetTeamID = A.FundForAssetTeamID
                WHERE MS.ManagerAccountID = :id
                GROUP BY SFAT.SharesForAssetTeamID ASC
                ";
            $s = $this->db->prepare($sql);
            $s->setFetchMode(PDO::FETCH_CLASS, TeamsModel::class);
            $s->execute([':id' => $this->manager->ManagerAccountID]);
            return $s->fetchAll();
        }
        return [];
    }

    private function isManager(): bool
    {
        $role = Config::MANAGER_TERM;
        return has_inclusion_of($role, array_column($this->getRoles(), 'RoleName'));
    }

}