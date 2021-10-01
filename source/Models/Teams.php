<?php

namespace CD\Models;

use CD\Core\DB\BaseDBModel;
use Exception;
use PDO;

class Teams extends BaseDBModel
{
    public function tableName(): string
    {
        return 'AssetTeams';
    }

    public function columns(): array
    {
        return ['*'];
    }

    public function primaryKey(): string
    {
        return 'AssetTeamID';
    }

    public function getTeams()
    {
        $s = $this->db->prepare('CALL GetAllTeams();');
        $s->execute();
        $s->setFetchMode(PDO::FETCH_CLASS, Team::class);
        return $s->fetchAll();
    }

    public function getTeamByStatus(int $status)
    {
        $s = $this->db->prepare('CALL GetTeamByStatus(:status)');
        $s->execute(['status' => $status]);
        return $s->fetchAll();
    }

    public function getTeamBySlug(string $slug)
    {
        $s = $this->db->prepare('CALL GetTeamBySlug(:slug)');
        $s->setFetchMode(PDO::FETCH_CLASS, Team::class);
        $s->execute(['slug' => $slug]);
        return $s->fetch();
    }

    public function getTeamByID($id)
    {
        $s = $this->db->prepare('CALL GetTeamByID(:id)');
        $s->setFetchMode(PDO::FETCH_CLASS, Team::class);
        $s->execute(['id' => $id]);
        return $s->fetch();
    }

    public function getTeamTypes()
    {
        $s = $this->db->prepare('SELECT * FROM TeamTypes');
        $s->execute();
        return $s->fetchAll();
    }

    public function getRoninAddresses()
    {
        $s = $this->db->prepare("SELECT AT.AssetTeamID AS id, AxieTeamTrackerAddress AS ronin, AxieTeamCurrentSLPBalance AS latest_balance, AxieTeamNextSLPClaim AS next_claim, AxieTeamTotalSLPFarmed as total_slp, TeamProfitShare AS royalty FROM AssetTeams AT JOIN AxieTeams AXT ON AXT.AssetTeamID = AT.AssetTeamID JOIN TeamTypes TT ON TT.TeamTypeID = AT.TeamTypeID");
        $s->execute();
        return $s->fetchAll();
    }

    public function updateTeamData($id, $data): bool
    {
        $vals = '';
        foreach ($data as $key => $value) {
            $vals .= $key . ' = :' . strtolower($key) . ', ';
        }
        $vals = rtrim($vals, ', ');
        $sql = "UPDATE AxieTeams SET $vals WHERE AssetTeamID = :id";
        $s = $this->db->prepare($sql);
        foreach ($data as $key => $row) {
            $s->bindValue(':' . strtolower($key), $row);
        }
        $s->bindValue(':id', $id);
        return $s->execute();
    }

    public function updateDailySLP($id, $value): bool
    {
        $sql = "INSERT INTO DailySLPGrind (AxieTeamID, DailySLPGrindAmount) VALUES (:id, :val)";
        $s = $this->db->prepare($sql);
        $s->bindValue(':id', $id);
        $s->bindValue(':val', $value);
        return $s->execute();
    }

    public function newFortnight($amount, $date): int
    {
        $sql = "INSERT INTO FortnightAxieWithdrawals (FortnightAxieWithdrawalTotalSLP, FortnightAxieWithdrawalIsDistributed, FortnightAxieWithdrawalDate) VALUES (:amt, 1, :date)";
        $s = $this->db->prepare($sql);
        $s->bindValue(':amt', $amount);
        $s->bindValue(':date', $date);
        $s->execute();
        return $this->db->lastInsertId();
    }

    public function newTeamPayout($id, $f_id, $amt, $date, $rate): int
    {
        $sql = "INSERT INTO AxieTeamPayouts (AxieTeamPayoutTotalSLP, AxieTeamPayoutShareRate, FortnightAxieWithdrawalID, AxieTeamID, AxieTeamPayoutDate) VALUES (:amt, :rate, :f_id, :id, :date)";
        $s = $this->db->prepare($sql);
        $s->bindValue(':amt', $amt);
        $s->bindValue(':rate', $rate);
        $s->bindValue(':date', $date);
        $s->bindValue(':id', $id);
        $s->bindValue(':f_id', $f_id);
        $s->execute();
        return $this->db->lastInsertId();
    }

    public function newCDPayout($id, $amt, $date, $rate): bool
    {
        $sql = "INSERT INTO ChainDrawerAxiePayouts (ChainDrawerAxiePayoutTotalSLP, AxieTeamPayoutID, ChainDrawerAxiePayoutDate, ChainDrawerAxiePayoutShareRate) VALUES (:amt, :id, :date, :rate)";
        $s = $this->db->prepare($sql);
        $s->bindValue(':amt', $amt);
        $s->bindValue(':rate', $rate);
        $s->bindValue(':date', $date);
        $s->bindValue(':id', $id);
        return $s->execute();
    }

    public function newManagerPayout($amt, $m_id, $last_bal, $date, $rate, $p_id): bool
    {
        $sql = "INSERT INTO ManagerPayouts (ManagerPayoutTotalSLP, ManagerAccountID, ManagerPayoutLastSLPBalance, ManagerPayoutDate, ManagerPayoutShareRate, AxieTeamPayoutID) VALUES (:amt, :m_id, :last_bal, :date, :rate, :p_id)";
        $s = $this->db->prepare($sql);
        $s->bindValue(':amt', $amt);
        $s->bindValue(':m_id', $m_id);
        $s->bindValue(':last_bal', $last_bal);
        $s->bindValue(':date', $date);
        $s->bindValue(':rate', $rate);
        $s->bindValue(':p_id', $p_id);
        return $s->execute();
    }

    public function updateTeamLifetimeSLP($id, $amt): bool
    {
        $sql = "UPDATE AxieTeams SET AxieTeamTotalSLPFarmed = AxieTeamTotalSLPFarmed + :amt WHERE AssetTeamID = :id";
        $s = $this->db->prepare($sql);
        $s->bindValue(':amt', $amt);
        $s->bindValue(':id', $id);
        return $s->execute();
    }

    public function getIncomingClaims($limit = 5)
    {
        $sql = "SELECT AssetTeamName, AxieTeamCurrentSLPBalance, AxieTeamTrackerAddress, AxieTeamNextSLPClaim FROM AssetTeams AT INNER JOIN AxieTeams AXT ON AXT.AssetTeamID = AT.AssetTeamID ORDER BY AxieTeamNextSLPClaim LIMIT :limit";
        $s = $this->db->prepare($sql);
        $s->execute([':limit' => $limit]);
        return $s->fetchAll();
    }

    public function teamNameTaken($slug): bool
    {
        $sql = "SELECT AssetTeamName FROM AssetTeams WHERE AssetTeamSlug = :slug LIMIT 1";
        $s = $this->db->prepare($sql);
        $s->execute(['slug' => $slug]);
        return $s->rowCount();
    }

    /**
     * @throws Exception
     */
    public function saveNewTeam($data): bool
    {
        try {
            $this->db->beginTransaction();

            $sql = "INSERT INTO AssetTeams (AssetTeamName, PlayerID, AssetTeamSlug, AssetTeamValue, TeamTypeID, AssetTeamDateAdded, AssetPlatformID) VALUES (:name, 1, :slug, :value, :type, :date, 1)";
            $s = $this->db->prepare($sql);
            $s->bindValue(':name', ucwords($data['team_name']));
            $s->bindValue(':slug', $data['slug']);
            $s->bindValue(':value', $data['team_value']);
            $s->bindValue(':type', $data['team_type']);
            $s->bindValue(':date', $data['date_established']);
            $s->execute();

            $last_id = $this->db->lastInsertId();
            $sql = "INSERT INTO AxieTeams (AxieTeamTrackerAddress, AssetTeamID) VALUES (:address, :tid)";
            $s = $this->db->prepare($sql);
            $s->bindValue(':address', $data['tracker_address']);
            $s->bindValue(':tid', $last_id);
            $s->execute();

            $this->db->commit();
            return true;
        } catch (Exception | \PDOException $e) {
            $this->db->rollBack();
            throw new Exception($e->getMessage(), 500);
        }
    }

}