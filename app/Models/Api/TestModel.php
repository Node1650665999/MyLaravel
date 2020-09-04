<?php

namespace App\Models\Api;
use App\Models\BaseModel;

class TestModel extends BaseModel
{
    protected $table = 'users';

    public function userInfo($userId)
    {
        /*$where = [['id','=',22], ['name','=','tclxx']];
        $data = $this->getSingle($where);
        dd($data);*/

        $where = [['id', 'in', [22, 23]], ['name', 'like', '%xx%']];
        $order = ['id' => 'desc', 'created_at' => 'desc'];
        $data  = $this->getMultiple($where, null, $order);
        dd($this->getQuerySql());
    }
}
