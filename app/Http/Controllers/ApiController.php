<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class ApiController extends Controller
{
    public $loginAfterSignUp = true;

    public function register(Request $request)
    {
        $user           = new User();
        $user->name     = $request->name;
        $user->phone    = $request->phone;
        $user->password = bcrypt($request->password);
        $user->save();

        if ($this->loginAfterSignUp) {
            return $this->login($request);
        }

        return $this->toJson(1, '成功');
    }

    /**
     * 生成 token
     * @desc 创建用户token最常用的方式就是通过登录实现,认证通过则返回token,否则认证失败返回false
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $input = $request->only('phone', 'name');
        $token = null;

//        $token = $this->generateToken();
        //$token = auth('api')->attempt($input);
        if (!$token = JWTAuth::attempt($input)) {
            return $this->toJson(500, '账号或密码不正确');
        }

        return $this->toJson(1, '成功', ['token' => $token]);
    }

    /**
     * 生成token
     * @param $input
     * @return mixed
     */
    private function generateToken($input=[])
    {
        $token = JWTAuth::setToken('foo.bar.baz');

        //$token = auth('api')->attempt($input);
        //token  = JWTAuth::fromUser($user);

        /*$customClaims = ['foo' => 'bar', 'baz' => 'bob'];
        $payload = JWTFactory::make($customClaims);
        $token = JWTAuth::encode($payload);*/

//        $token = JWTAuth::attempt($input);

        return $token;
    }

    /**
     * 获取token
     * @return mixed
     */
    private function getToken()
    {
        $token = JWTAuth::getToken();
//        $token = JWTAuth::parseToken()->getToken();
        return $token;
    }

    /**
     * 获取用户
     * @param string $token
     * @return mixed
     */
    private function getUser($token='')
    {
        //guard 指的是config/auth.php 中 guard 选项中的 web/api/...
        $guard = 'api';

//        $user = Auth::user(); //Illuminate\Support\Facades\Auth
//        $user = Auth::guard($guard)->user();
//        $user = auth($guard)->user();
        $user = JWTAuth::authenticate($token);
//        $user = JWTAuth::parseToken()->authenticate();

        return $user;
    }

    /**
     * 销毁token
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        $token = $request->input('token');

        try {
            JWTAuth::invalidate($token);
            //auth($guard)->logout(); //$guard 可以是 web/api

            return $this->toJson(1, '成功');

        } catch (JWTException $exception) {

            return $this->toJson(500, '失败');
        }
    }

    public function freshToken()
    {

    }

    /**
     * 获取用户信息
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAuthUser(Request $request)
    {
        $token  = $request->input('token');
//        $token    = $this->getToken();
        $user   = $this->getUser($token);

        return $this->toJson(1, '成功', ['user' => $user]);
    }

    /**
     * 获取token的过期时间
     * @return float|int
     */
    public function tokenTTL()
    {
        //$ttl = Auth::guard($guard)->factory()->getTTL() * 60;
        $ttl = auth('api')->factory()->getTTL() * 60;

        return $ttl;
    }

    /**
     * 输出结果
     * @param $code
     * @param $msg
     * @param array $data
     * @return \Illuminate\Http\JsonResponse
     */
    private function toJson($code, $msg, $data=[])
    {
        return response()->json([
            'code' => $code ?? 1,
            'msg'  => $msg ?? '成功',
            'data' => $data
        ]);
    }
}
