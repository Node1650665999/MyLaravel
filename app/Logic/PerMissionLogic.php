<?php
/**
 * PerMission-Logic
 * @Author: tcl
 */
namespace App\Logic;
use App\Models\PmsAdminRoleModel;
use App\Models\PmsAdminPerMissionModel;
use App\Models\PmsRolePerMissionModel;
use App\Models\PmsPerMissionModel;
/**
 * Class PerMissionLogic
 * @package App\Logic
 */
class PerMissionLogic extends BaseLogic {
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
    private $adminHasPerMissionModel = null;

    /**
     * @var null
     */
    private $permissionModel         = null;

    /**
     * PerMissionLogic constructor.
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function __construct()
    {
        parent::__construct();
        $this->permissionModel         = new PmsPerMissionModel;
        $this->roleHasPerMissionModel  = new PmsRolePerMissionModel;
        $this->adminHasRoleModel       = new PmsAdminRoleModel;
        $this->adminHasPerMissionModel = new PmsAdminPerMissionModel;
    }

    //=======================================权限管理=================================

    /**
     * 新增
     * @param $input
     * @return bool
     */
    public function create($input)
    {
        $data = $this->buildData($input);
        if(false == $this->permissionModel->insertSingle($data))
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

        if(false == $this->permissionModel->updateRows($where, $data))
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
            'appid'         => $this->appId(),
            'name'          => trim($input['name']),
            'route'         => trim($input['route']),
            'permission'    => trim($input['permission']),
            'parent_id'     => intval($input['parent_id']),
            'sort'          => intval($input['sort']),
            'type'          => intval($input['type']),
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
        return $this->permissionModel->getSingle($where);
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
        //删除当前菜单和它的子级
        $data            = $this->permissionModel->getMultiple([]);
        $ids             = $this->subIds($data, $id);
        array_unshift($ids, $id);

        $where = [['id', 'in', $ids]];
        if(false == $this->permissionModel->deleteRows($where))
        {
            return $this->setReturn('失败');
        }

        return $this->setReturn('成功', true);
    }

    /**
     * 查找子级
     * @param $data
     * @param $pId
     * @param array $ids
     * @return array
     */
    private function subIds($data, $pId, &$ids=[])
    {
        foreach($data as $k => $v)
        {
            if($v['parent_id'] == $pId)
            {
                $ids[]= $v['id'];
                $this->subIds($data, $v['id'], $ids);
            }
        }
        return $ids;
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

        if($input['route'])
        {
            $where[] = ['route', 'like', "%{$input['route']}%"];
        }

        if($input['permission'])
        {
            $where[] = ['permission', 'like', "%{$input['permission']}%"];
        }

        $data = $this->permissionModel->getPageList('*', $where, ['id' => 'asc'], $pageSize);
        return $this->setReturn('成功', true,  $data);
    }

    //=======================================权限分配=================================

    /**
     * 用户拥有的权限
     * @param $uid
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function userHasPermission($uid)
    {
        $where     = [['admin_id', '=', $uid]];
        $list      = $this->adminHasPerMissionModel->getMultiple($where, 'permission_id');
        return ['permission_ids' => array_column($list, 'permission_id')];
    }

    /**
     * 检查登录用户是否有权限
     * @param $permisson
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function matchPermission($permission)
    {
        list($menus, $permissions) = $this->allPermissions($this->adminId());
        return in_array(trim($permission), $permissions);
    }

    /**
     * 为用户分配权限
     * @param $input
     * @return bool
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function assignPerMissionForUser($input)
    {
        $adminId       = $input['id'];
        $permissionIds = $input['permission_ids'];
        $permissionData = $this->handleAssignData($permissionIds, ['admin_id' => $adminId]);

        $this->adminHasPerMissionModel->startTrans();

        //删除已有的权限
        $this->adminHasPerMissionModel->deleteRows([['admin_id','=',$adminId]]);

        //插入新的权限
        if($permissionData && ! $this->adminHasPerMissionModel->insertMultiple($permissionData))
        {
            $this->adminHasPerMissionModel->rollback();
            return $this->setReturn('失败');
        }

        $this->adminHasPerMissionModel->commit();

        return $this->setReturn('成功', true);
    }

    /**
     * 校色拥有的权限
     * @param $input
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function roleHasPermission($input)
    {
        $where  = [['role_id', '=', $input['id']]];
        $list   = $this->roleHasPerMissionModel->getMultiple($where, 'permission_id');
        return ['permission_ids' => array_column($list, 'permission_id')];
    }

    /**
     * 为角色分配权限
     * @param $input
     * @return bool
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function assignPerMissionForRole($input)
    {
        $roleId         = $input['id'];
        $permissionIds  = $input['permission_ids'];
        $permissionData = $this->handleAssignData($permissionIds, ['role_id' => $roleId]);

        $this->roleHasPerMissionModel->startTrans();

        //删除已有的权限
        $this->roleHasPerMissionModel->deleteRows([['role_id','=',$roleId]]);

        //插入新的权限
        if($permissionData && ! $this->roleHasPerMissionModel->insertMultiple($permissionData))
        {
            $this->roleHasPerMissionModel->rollback();
            return $this->setReturn('失败');
        }

        $this->roleHasPerMissionModel->commit();

        return $this->setReturn('成功', true);
    }

    /**
     * 处理权限分配数据
     * @param $permissionIds
     * @param $item
     * @return false|string[]
     */
    private function handleAssignData($permissionIds, $item)
    {
        $permissionIds = explode(',', $permissionIds);
        $permissionIds = $this->filterPermissionIds($permissionIds);

        $permissionData = [];

        foreach ($permissionIds as $val)
        {
            $permissionData[] = array_merge(['permission_id' => $val], $item);
        }

        return $permissionData;
    }


    /**
     * 用户拥有的所有权限
     * @param $uid
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function allPermissions($uid=null)
    {
        $permissionIds   = $this->getPerMissionIds($uid);
        $where           = [['id', 'in', $permissionIds]];
        $order           = ['sort' => 'asc'];
        $data            = $this->permissionModel->getMultiple($where, "*", $order);

        $menuList        =  $this->tree($data, 0);
        $permissions     = array_column($data, 'permission');

        return [$menuList, $permissions];
    }

    /**
     * 获取用户拥有的所有权限
     * @param $uid
     * @return array
     * @throws \think\db\exception\BindParamException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    private function getPerMissionIds($uid)
    {
        // 获取角色拥有的权限
        $ahrTable = $this->adminHasRoleModel->getTableName();
        $rhpTable = $this->roleHasPerMissionModel->getTableName();
        $sql  = "select rhp.permission_id from {$ahrTable} ahr ";
        $sql .= "left join {$rhpTable} rhp on ahr.role_id=rhp.role_id ";
        $sql .= "where ahr.admin_id={$uid}";
        $rolePerMissionIds = $this->roleHasPerMissionModel->querySql($sql);
        $rolePerMissionIds = array_column($rolePerMissionIds, 'permission_id');

        // 获取用户拥有的权限
        $where              = [['admin_id','=', $uid]];
        $adminPerMissionIds = $this->adminHasPerMissionModel->getMultiple($where);
        $adminPerMissionIds = array_column($adminPerMissionIds, 'permission_id');

        //合并权限
        $permissionIds = array_merge($rolePerMissionIds, $adminPerMissionIds);

        return array_values(array_unique($permissionIds));
    }

    /**
     * 剔除已删除的权限
     * @param $permissionIds
     * @return array
     */
    private function filterPermissionIds($permissionIds)
    {
        //剔除已删除的系统权限
        $systemPmsIds  = $this->permissionModel->column("id");
        $permissionIds = array_intersect(array_filter($permissionIds), $systemPmsIds);
        return array_values(array_unique($permissionIds));
    }

    /**
     * 生成无限级分类树
     * @param $data
     * @param $pId
     * @param $depth
     * @return array
     */
    private function tree($data, $pId, $depth=0)
    {
        $tree = [];
        foreach($data as $k => $v)
        {
            if($v['parent_id'] == $pId)
            {
                $v['children'] =  $this->tree($data, $v['id'], $depth+1);
                $tree[] = [
                    'id'             => $v['id'],
                    'depth'          => $depth,
                    'parent_id'      => $v['parent_id'],
                    'name'           => $v['name'],
                    'type'           => $v['type'],
                    'route'          => $v['route'],
                    'permission'     => $v['permission'],
                    'children'       => $v['children']
                ];
            }
        }
        return $tree;
    }

    /**
     * 遍历权限树
     * @param $tree
     * @param array $arr
     * @return array
     */
    private function expand($tree, &$arr=[])
    {
        foreach ($tree as $v)
        {
            array_push($arr, $v['permission']);
            if($v['children'])
            {
                $this->expand($v['children'], $arr);
            }
        }

        return $arr;
    }



}
