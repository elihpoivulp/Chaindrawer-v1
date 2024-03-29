<?php

namespace CD\Models;

use CD\Core\DB\BaseDBModel;
use PDO;

class Withdrawals extends BaseDBModel
{

    public function tableName(): string
    {
        return '';
    }

    public function columns(): array
    {
        return ['*'];
    }

    public function primaryKey(): string
    {
        return '';
    }

    public function getAllBanks()
    {
        $s = $this->db->prepare("SELECT * FROM Banks");
        $s->execute();
        return $s->fetchAll();
    }

    public function getAllEMoneys()
    {
        $s = $this->db->prepare("SELECT * FROM EMoneys");
        $s->execute();
        return $s->fetchAll();
    }

    public function getBankByID(int $id)
    {
        $s = $this->db->prepare("SELECT BankName FROM Banks WHERE BankID = :id");
        $s->execute(['id' => $id]);
        return $s->fetch();
    }

    public function getEmoneyByID(int $id)
    {
        $s = $this->db->prepare("SELECT EMoneyName FROM EMoneys WHERE EMoneyID = :id");
        $s->execute(['id' => $id]);
        return $s->fetch();
    }

    public function addToWithdrawHistory(array $data, array $recipient): int
    {
        $cols = 'WithdrawalRequestSLPAmount, WithdrawalRequestAXSAmount, WithdrawalRequestMethod, WithdrawalRequestRemSLPBalance, WithdrawalRequestRemAXSBalance, ManagerAccountID, WithdrawalRequestDate';
        $keys = ':slp_amt, :axs_amt, :method, :rem_slp_bal, :rem_axs_bal, :manager, :date';
//        'binance', 'bank', 'emoney', 'ronin'
        function addBind($s, array $data) {
            foreach ($data as $k => $v) {
                $s->bindValue($k, $v);
            }
        }
        switch ($data['method']) {
            case 'binance':
                $cols .= ', BinanceEmail';
                $keys .= ', :binance';
                $data[':binance'] = $recipient['email'];
                break;
            case 'bank':
                $cols .= ', BankName, BankPhone, BankAccountName, BankAccountNumber';
                $keys .= ', :bname, :bphone, :baccname, :baccnum';
                $data[':bname'] = $recipient['bank_name'];
                $data[':bphone'] = $recipient['phone_number'];
                $data[':baccname'] = $recipient['account_name'];
                $data[':baccnum'] = $recipient['account_number'];
                break;
            case 'emoney':
                $cols .= ', EMoneyGateWay, EMoneyPhone, EMoneyAccountName';
                $keys .= ', :emoneygateway, :emoneyphone, :emoneyaccname';
                $data[':emoneygateway'] = $recipient['gateway'];
                $data[':emoneyphone'] = $recipient['phone'];
                $data[':emoneyaccname'] = $recipient['name'];
            case 'ronin':
                $cols .= ', RoninAddress';
                $keys .= ', :ronin';
                $data[':ronin'] = $recipient['ronin'];
                break;
        }
        $s = $this->db->prepare("INSERT INTO WithdrawalRequests ($cols) VALUES ($keys)");
        $s->bindValue(':slp_amt', $data['slp_amt']);
        $s->bindValue(':axs_amt', $data['axs_amt']);
        $s->bindValue(':method', $data['method']);
        $s->bindValue(':rem_slp_bal', $data['rem_slp_bal']);
        $s->bindValue(':rem_axs_bal', $data['rem_axs_bal']);
        $s->bindValue(':manager', $data['manager']);
        $s->bindValue(':date', $data['date']);
        addBind($s, $data);
        $s->execute();
        return $this->db->lastInsertId();
    }

    public function getWithdrawalDetails($id)
    {
        $sql = "SELECT 
                WR.*,
                CONVERT(WR.WithdrawalRequestID, CHAR) AS WithdrawalRequestID,
                WithdrawalAXSinPHP, WithdrawalSLPinPHP, WithdrawalSLPRate, WithdrawalAXSRate,
                CONCAT(UserFirstName, ' ', UserMiddleName, ' ', UserLastName) as FullName
                FROM WithdrawalRequests WR
                JOIN ManagerAccounts MA on WR.ManagerAccountID = MA.ManagerAccountID
                JOIN Users ON MA.UserID = Users.UserID
                LEFT JOIN Withdrawals W on WR.WithdrawalRequestID = W.WithdrawalRequestID
                WHERE WR.WithdrawalRequestID = :id
                ORDER BY WithdrawalRequestStatus DESC, WithdrawalRequestDate DESC, WithdrawalRequestDateCompleted DESC
                ";
        $s = $this->db->prepare($sql);
        $s->execute(['id' => $id]);
        return $s->fetch();
    }

    public function completeWithdrawal($id, $manager_id, $data): bool
    {
        $now = $data['WithdrawalDateProcessed'];
        $sql = "UPDATE WithdrawalRequests SET WithdrawalRequestStatus = 'completed', WithdrawalRequestDateCompleted = '$now' WHERE WithdrawalRequestID = :id";
        $s = $this->db->prepare($sql);
        $s->bindValue(':id', $id);
        if ($s->execute()) {
            $data['WithdrawalRequestID'] = $id;
            $cols = '(' . join(', ', array_keys($data)) . ')';
            $keys = '';
            foreach ($data as $index => $datum) {
                $keys .= ':' . $index . ', ';
            }
            $keys =  '(' . rtrim($keys, ', ') . ')';
            $sql = "INSERT INTO Withdrawals $cols VALUES $keys";
            $s = $this->db->prepare($sql);
            foreach ($data as $index => $datum) {
                $s->bindValue(':'. $index, $datum);
            }
            if ($s->execute()) {
                $sql = "UPDATE ManagerAccounts SET ManagerAccountCurrentSLPBalance = (ManagerAccountCurrentSLPBalance - :consumed) WHERE ManagerAccountID = :id";
                $s = $this->db->prepare($sql);
                $s->bindValue(':consumed', $data['WithdrawalSLPAmount']);
                $s->bindValue(':id', $manager_id);
                return $s->execute();
            }
        }
        return false;
    }

    public function cancelWithdrawal($id): bool
    {
        $sql = "DELETE FROM WithdrawalRequests WHERE WithdrawalRequestID = :id";
        $s = $this->db->prepare($sql);
        return $s->execute([':id' => $id]);
    }
}