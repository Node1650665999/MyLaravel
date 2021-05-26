<?php
/**
 * Admin-Logic
 * @Author: tcl
 */
namespace App\Logic;
use App\Models\PmsAdminModel;
use App\Models\PmsLoginModel;
use App\Logic\PerMissionLogic;
use App\Models\PmsAdminRoleModel;
use App\Logic\RoleLogic;

class AdminLogic extends BaseLogic {

    /**
     * @var null
     */
    private $adminModel            = null;

    /**
     * @var null
     */
    private $loginLogModel         = null;

    /**
     * @var null
     */
    private $adminHasRoleModel    = null;

    /**
     * @var null
     */
    private $permissionLogic       = null;

    /**
     * @var null
     */
    private $roleLogic             = null;

    /**
     * AdminLogic constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->adminModel               = new PmsAdminModel();
        $this->loginLogModel            = new PmsLoginModel();
        $this->permissionLogic          = new PerMissionLogic();
        $this->roleLogic                = new RoleLogic();
        $this->adminHasRoleModel        = new PmsAdminRoleModel();

    }

    /**
     * 用户登录
     * @param $param
     * @return bool
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function login($param)
    {
        $username = $param['username'];
        $passwd   = $param['passwd'];

        $where = [['username','=',$username]];
        $info = $this->info($where);

        // 账号检查
        if(! $info)
        {
            return $this->setReturn('管理员不存在');
        }

        // 密码检查
        if(md5($passwd) != $info['passwd'])
        {
            return $this->setReturn('密码错误');
        }

        // token 更新
        if(! $this->isTokenValid($info))
        {
            if(! $this->updateToken($username, $passwd))
            {
                return $this->setReturn('token 更新失败');
            }
        }

        $data = $this->loginData($where);

        //记录登录日志
        $this->loginLog($data['id'], $data['username']);

        return $this->setReturn('成功', true, $data);
    }

    /**
     * 登录数据
     * @param $where
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    private function loginData($where)
    {
        // 获取更新后的账户信息
        $info = $this->info($where);

        //菜单和权限
        list($menuList, $permissions) = $this->permissionLogic->allPermissions($info['id']);
        return [
            'id'            => $info['id'],
            'token'         => $info['token'],
            'token_expire'  => $info['token_expire'],
            'username'      => $info['username'],
            'menu'          => $menuList,
            'permission'    => $permissions
        ];
    }

    /**
     * 登录日志
     * @param $adminId
     * @param $adminName
     * @return false|int|string
     */
    private function loginLog($adminId, $adminName)
    {
        $request = request();
        $data = [
            'admin_id'   => $adminId,
            'admin_name' => $adminName,
            'ip'         => $request->ip(),
            'user_agent' => $request->header('User-Agent'),
        ];

        return $this->loginLogModel->insertSingle($data);
    }

    /**
     * 生成token
     * @param $account
     * @param $passwd
     * @return string
     */
    private function makeToken($account, $passwd)
    {
        $str = $account . '$**$' . $passwd . time();

        return md5($str);
    }

    /**
     * 更新token
     * @param $account
     * @param $passwd
     * @return int|string
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    private function updateToken($account, $passwd)
    {
        $token          = $this->makeToken($account, $passwd);
        $tokenExpier    = time() + 30 * 24 * 60 * 60; //一个月
        $where          = [['username', '=', $account]];
        $data           = [
            'token'         => $token,
            'token_expire'  => $tokenExpier,
            'updated_at'    => __CURRENT__
        ];
        return $this->adminModel->updateRows($where, $data);
    }

    /**
     * token 是否有效
     * @param $info
     * @return bool
     */
    public function isTokenValid($info)
    {
        //token校验
        if($info['token'] && $info['token_expire'] > time())
        {
            return true;
        }

        return false;
    }

    /**
     * 管理员信息
     * @param $where
     * @return array|false|\PDOStatement|string|\think\Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    private function info($where)
    {
        return $this->adminModel->getSingle($where);
    }

    /**
     * 新增管理员
     * @param $param
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function create($param)
    {
        $username = $param['username'];
        $passwd   = $param['passwd'];

        if($this->info([['username','=',$username]]))
        {
            return $this->setReturn('用户名已存在');
        }

        if($this->isSuper == false)
        {
            return $this->setReturn('普通管理员没有权限');
        }

        $data = [
            'appid'         => $this->appId(),
            'username'      => $username,
            'passwd'        => md5($passwd),
            'created_at'    => __CURRENT__
        ];
        if(false == $this->adminModel->insertSingle($data))
        {
            return $this->setReturn('新增失败');
        }

        return $this->setReturn('成功', true);
    }

    /**
     * 更新管理员信息
     * @param $param
     * @return bool
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function update($param)
    {
        $userId   = $param['id'];
        $username = $param['username'];
        $passwd   = $param['passwd'];

        if(! $this->info([['id', '=', $userId]]))
        {
            return $this->setReturn('用户不存在');
        }

        if($this->isSuper == false)
        {
            return $this->setReturn('普通管理员没有权限');
        }

        $where   = [['id', '=', $userId]];
        $data    = [
            'passwd'     => md5($passwd),
            'username'   => $username,
            'updated_at' => __CURRENT__
        ];

        if(false == $this->adminModel->updateRows($where, $data))
        {
            return $this->setReturn('更新失败');
        }

        $needLogin = $userId == $this->adminId() ? 1 : 0;

        return $this->setReturn('成功', true, ['need_login' => $needLogin]);
    }

    /**
     * 管理员列表
     * @param $param
     * @return bool
     * @throws \think\exception\DbException
     */
    public function list($param)
    {
        $where = [];

        if($param['username'])
        {
            $where[] = ['username', 'like', "%{$param['username']}%"];
        }

        $limit = $param['per_page'];
        $data = $this->adminModel->getPageList('*', $where, ['id' => 'desc'], $limit);
        return $this->setReturn('成功', true,  $data);
    }

    /**
     * 刷新权限
     * @param $input
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function refreshPermission($input)
    {
        $where   = [['id', '=', $input['id']]];
        return $this->loginData($where);
    }


    /**
     * 为用户分配角色
     * @param $input
     * @return bool
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function assignRole($input)
    {
        $adminId   =  $input['id'];
        $roleIds   =  $input['role_ids'];
        $roleIds   =  $this->filterRoleIds($roleIds);
        $data      = [];
        foreach ($roleIds as $roleId)
        {
            $data[] = ['admin_id' => $adminId, 'role_id' => $roleId];
        }

        $this->adminHasRoleModel->startTrans();

        $this->adminHasRoleModel->deleteRows([['admin_id', '=', $adminId]]);

        if(false == $this->adminHasRoleModel->insertMultiple($data))
        {
            $this->adminHasRoleModel->rollback();
            return $this->setReturn('失败');
        }

        $this->adminHasRoleModel->commit();

        return $this->setReturn('成功', true);
    }

    /**
     * 剔除已删除的角色
     * @param $roleIds
     * @return array
     */
    private function filterRoleIds($roleIds)
    {
        $roleIds    = array_filter(explode(',', $roleIds));
        $allRoleIds = $this->roleLogic->allRoleIds();
        return array_values(array_unique(array_intersect($roleIds, $allRoleIds)));
    }

    /**
     * 用户拥有的角色
     * @param $input
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function hasRole($input)
    {
        $adminId = $input['id'];
        $where  = [['admin_id', '=', $adminId]];
        $list   = $this->adminHasRoleModel->getMultiple($where, 'role_id');
        return ['role_ids' => array_column($list, 'role_id')];
    }

    /**
     * 登录日志
     * @param $input
     * @return bool
     * @throws \think\exception\DbException
     */
    public function loginLogs($input)
    {
        $pageSize = $input['page_size'];
        $where    = $this->loginLogsWhere($input);
        $data     = $this->loginLogModel->getPageList('*', $where, ['id' => 'desc'], $pageSize);
        return $this->setReturn('成功', true,  $data);
    }

    /**
     * @param $input
     * @return array
     */
    private function loginLogsWhere($input)
    {
        $where    = [];

        if($input['admin_name'])
        {
            $where[] = ['admin_name', 'like', "%{$input['admin_name']}%"];
        }

        if($input['ip'])
        {
            $where[] = ['ip', '=', $input['ip']];
        }

        if($input['star_at'])
        {
            $where[] = ['create_time' ,'>', $input['star_at']];
        }

        if($input['end_at'])
        {
            $where[] = ['create_time' ,'<', $input['end_at']];
        }

        return $where;
    }
}
