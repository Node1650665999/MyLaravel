<?php

namespace App\Logic;
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
}
