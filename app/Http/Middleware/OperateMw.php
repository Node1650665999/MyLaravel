<?php
/**
 * 操作日志中间件
 * User: admin
 * Date: 2018/10/15 0015
 * Time: 09:49
 */

namespace App\Http\Middleware;
use App\Logic\OperateLogic;

/**
 * Class OperateMw
 * @package app\common\middleware
 */
class OperateMw
{
    /**
     * @param $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, \Closure $next)
    {
        //操作日志
        $operateLogic = new OperateLogic();
        $operateLogic->create($request);

        return $next($request);
    }
}
