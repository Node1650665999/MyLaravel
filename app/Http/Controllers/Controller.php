<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Response;
class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * 参数响应
     * @param int $code
     * @param string $msg
     * @param array $data
     * @return \Illuminate\Http\JsonResponse
     */
    public function apiJson($code=200, $msg='成功', $data=[])
    {
        $data = [
            'code'  => $code,
            'msg'   => $msg,
            'data'  => $data ?: null,
        ];

        return Response::json($data);
    }

    /**
     * 获取参数
     * @param null $name
     * @param null $default
     * @return mixed|null
     */
    public function input($name=null, $default=null)
    {
        return request()->input($name, $default);
    }
}
