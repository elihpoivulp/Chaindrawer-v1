<?php


namespace CD\Models;


use CD\Core\DB\DB;
use CD\Core\Models\DBFormModel;
use Exception;

class UserRolesModel extends DBFormModel
{
    public int $RoleID;

    public function __construct()
    {
    }

    public function tableName(): string
    {
        return 'UserRoles';
    }

    public function primaryKey(): string
    {
        return 'UserRoleID';
    }

    public function columns(): array
    {
        return ['*'];
    }

    /**
     * @throws Exception
     */
    public function getUserRoleData(int $user_id)
    {
        return $this->select($this->columns())->where([$this->primaryKey() => $user_id])->fetch();
    }
}