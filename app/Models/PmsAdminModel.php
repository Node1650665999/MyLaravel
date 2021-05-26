<?php
namespace App\Models;
class PmsAdminModel extends BaseModel
{
    /**
     * @var string 管理员表
     */
    protected $table = 'pms_admin';

    /**
     * 管理员信息
     * @param $where
     * @return array|false|\PDOStatement|string|\think\Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function info($where)
    {
        return $this->getSingle($where);
    }
}
