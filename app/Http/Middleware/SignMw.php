<?php
/**
 * 签名中间件
 */
namespace App\Http\Middleware;
use Closure;
class SignMw
{
    const APP_SECRET = 'i#o0noj&K#Kisy)uHU';

    /**
     * @param  \Illuminate\Http\Request  $request
     * @param Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $params = $request->input(); //获取参数
        empty($params) && $params = [];

        //签名
        if (empty($params['sign']))
        {
            return apiResponse('3000', '[sign]缺失');
        }

        $except = []; //不作签名的字段,sign已经在方法中排除
        $sign   = $this->make($params, self::APP_SECRET, $except);

        if ($sign !== trim($params['sign']))
        {
            return apiResponse('3000', '[sign]签名错误');
        }

        return $next($request);
    }

    /**
     * 签名
     * @param array $params //生成签名的参数
     * @param string $secret //秘钥
     * @param array $exceptPm //需要排除的参数名
     * @return string
     */
    public function make($params = [], $secret = '', $exceptPm = [])
    {
        ksort($params);
        array_push($exceptPm, 'sign');
        $str = '';
        foreach ($params as $key => $val) {
            if (!in_array($key, $exceptPm)) {
                $str .= $key . $val;
            }
        }
        $str = $secret . $str;
        $sign = strtoupper(md5($str));
        return $sign;
    }


}
