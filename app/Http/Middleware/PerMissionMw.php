<?php
/**
 * 权限中间件
 * User: admin
 * Date: 2018/10/15 0015
 * Time: 09:49
 */

namespace App\Http\Middleware;
use app\index\logic\PerMissionLogic;
/**
 * Class PerMissionMw
 * @package
 */
class PerMissionMw
{
    /**
     * @param $request
     * @param \Closure $next
     * @param string $permission
     * @return \Illuminate\Http\JsonResponse|mixed|\think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function handle($request, \Closure $next, $permission='')
    {
        $permissionLogic = new PerMissionLogic();
        if($permission &&  ! $permissionLogic->matchPermission($permission))
        {
            return apiResponse(3004, "没有权限 [{$permission}]");
        }

        return $next($request);
    }
}
