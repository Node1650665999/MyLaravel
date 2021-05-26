<?php
/**
 * Logic
 * @Author: tcl
 */
namespace App\Logic;
use App\Models\PmsRoleModel;
use App\Models\PmsAdminRoleModel;
use App\Models\PmsRolePerMissionModel;
class RoleLogic extends BaseLogic {

    /**
     * @var null
     */
    private $adminHasRoleModel      = null;

    /**
     * @var null
     */
    private $roleHasPerMissionModel  = null;

    /**
     * @var null
     */
    private $roleModel               = null;

    /**
     * PerMissionLogic constructor.
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function __construct()
    {
        parent::__construct();
        $this->roleModel               = new PmsRoleModel;
        $this->adminHasRoleModel       = new PmsAdminRoleModel;
        $this->roleHasPerMissionModel  = new PmsRolePerMissionModel;
    }

    /**
     * 新增
     * @param $input
     * @return bool
     */
    public function create($input)
    {
        $data = $this->buildData($input);

        if(false == $this->roleModel->insertSingle($data))
        {
            return $this->setReturn('失败');
        }

        return $this->setReturn('成功', true);
    }

    /**
     * 编辑
     * @param $input
     * @return bool
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function edit($input)
    {
        $data = $this->buildData($input);
        $where = [['id', '=', $data['id']]];

        if(false == $this->roleModel->updateRows($where, $data))
        {
            return $this->setReturn('失败');
        }

        return $this->setReturn('成功', true);
    }

    /**
     * 构造数据
     * @param $input
     * @return array
     */
    private function buildData($input)
    {
        $data = [
            'name'          => $input['name'],
            'appid'         => $this->appId()
        ];

        if($input['id'])
        {
            $data['id']         = $input['id'];
            $data['updated_at'] = __CURRENT__;
        }
        else
        {
            $data['created_at'] = __CURRENT__;
        }

        return $data;
    }

    /**
     * 详情
     * @param $id
     * @return array|false|\PDOStatement|string|\think\Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function info($id)
    {
        $where = [['id', '=', $id]];
        return $this->roleModel->getSingle($where);
    }

    /**
     * 删除
     * @param $id
     * @return bool
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function delete($id)
    {
        $where = [
            ['id',    '=', $id],
            ['appid', '=', $this->appId()],
        ];
        $this->roleModel->startTrans();

        //删除用户拥有的角色
        $this->adminHasRoleModel->deleteRows([['role_id', '=', $id]]);

        //删除角色上的权限
        $this->roleHasPerMissionModel->deleteRows([['role_id', '=', $id]]);

        //删除角色
        if(false == $this->roleModel->deleteRows($where))
        {
            $this->roleModel->rollback();
            return $this->setReturn('失败');
        }

        $this->roleModel->commit();

        return $this->setReturn('成功', true);
    }

    /**
     * 列表
     * @param $input
     * @return bool
     * @throws \think\exception\DbException
     */
    public function list($input)
    {
        $pageSize = $input['page_size'];
        $where    = [];
        if($input['name'])
        {
            $where[] = ['name', 'like', "%{$input['name']}%"];
        }

        $data = $this->roleModel->getPageList('*', $where, ['id' => 'asc'], $pageSize);
        return $this->setReturn('成功', true,  $data);
    }

    /**
     * 所有的角色id
     * @return array
     */
    public function allRoleIds()
    {
        return$this->roleModel->column("id");
    }
}
