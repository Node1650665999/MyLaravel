<?php

namespace App\Logic;
use App\Models\PmsAdminModel;
/**
 * Class BaseLogic
 * @package app\common\logic
 */
class BaseLogic
{
    /**
     * @var array 返回信息
     */
    protected $returnArr = [];

    public function __construct()
    {
        //todo
    }

    /**
     * 设置返回信息
     * @param $status
     * @param $msg
     * @return bool
     */
    public function setReturn($msg, bool $status = false, $data = [])
    {
        $this->returnArr = [
            'status' => $status,
            'msg' => $msg,
            'data' => $data
        ];
        return $status;
    }

    /**
     * 获取返回信息
     * @return array
     */
    public function getReturn()
    {
        return $this->returnArr;
    }

    /**
     * 获取状态
     * @return mixed
     * @throws \Exception
     */
    public function getStatus()
    {
        $status = $this->returnArr['status'];

        if (is_null($status)) {
            throw new \Exception('canot get status of Logic');
        }

        return $status;
    }

    /**
     * 获取消息
     * @return mixed
     * @throws \Exception
     */
    public function getMsg()
    {
        $msg = $this->returnArr['msg'];

        if (is_null($msg)) {
            throw new \Exception('canot get errMsg of Logic');
        }

        return $msg;
    }

    /**
     * 获取数据
     * @return mixed
     * @throws \Exception
     */
    public function getData()
    {
        $data = $this->returnArr['data'];

        if (is_null($data)) {
            throw new \Exception('canot get data of Logic');
        }

        return $data;
    }

    /**
     * 获取 adminId
     * @return mixed|null
     */
    protected function adminId()
    {
        return $this->adminData("id");
    }

    /**
     * 获取 adminName
     * @return mixed|null
     */
    protected function adminName()
    {
        return $this->adminData('username');
    }

    /**
     * 管理员信息
     * @return array|false|\PDOStatement|string|\think\Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function adminData($filed = null)
    {
        $model = new PmsAdminModel;
        $token = request()->input('token');
        $where = [['token', '=', $token]];
        $info  = $model->getSingle($where);
        return $filed ? $info[$filed] : $info;
    }

    /**
     * 应用ID
     * @return int
     */
    public function appId()
    {
        return 1;
    }




}
