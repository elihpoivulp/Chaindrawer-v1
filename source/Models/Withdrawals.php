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

    public function addToWithdrawHistory(array $data): int
    {
        $s = $this->db->prepare("INSERT INTO WithdrawalRequests (WithdrawalRequestSLPAmount, WithdrawalRequestAXSAmount, WithdrawalRequestMethod, WithdrawalRequestRemSLPBalance, WithdrawalRequestRemAXSBalance, ManagerAccountID) VALUES (:slp_amt, :axs_amt, :method, :rem_slp_bal, :rem_axs_bal, :manager)");
        $s->bindValue(':slp_amt', $data['slp_amt']);
        $s->bindValue(':axs_amt', $data['axs_amt']);
        $s->bindValue(':method', $data['method']);
        $s->bindValue(':rem_slp_bal', $data['rem_slp_bal']);
        $s->bindValue(':rem_axs_bal', $data['rem_axs_bal']);
        $s->bindValue(':manager', $data['manager']);
        $s->execute();
        return $this->db->lastInsertId();
    }
}