<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class ApiController extends Controller
{
    public $loginAfterSignUp = true;

    public function register(Request $request)
    {
        $user = new User();
        $user->name     = $request->name;
        $user->phone    = $request->phone;
        $user->password = bcrypt($request->password);
        $user->save();

        if ($this->loginAfterSignUp) {
            return $this->login($request);
        }

        return response()->json([
            'success' => true,
            'data' => []
        ], 200);
    }

    /**
     * 生成 token
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $input = $request->only('phone', 'password');
        $token = null;

        //$token = auth('api')->attempt($input);
        if (!$token = JWTAuth::attempt($input)) {
            return response()->json([
                'success' => false,
                'message' => '账号或密码不正确',
            ]);
        }

        return response()->json([
            'success' => true,
            'token' => $token,
        ]);
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
            //auth('api')->logout();

            return response()->json([
                'success' => true,
                'message' => '登出成功'
            ]);
        } catch (JWTException $exception) {
            return response()->json([
                'success' => false,
                'message' => '登出失败'
            ]);
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
        $token = $request->input('token');

        $user = JWTAuth::authenticate($token);

        //$user = auth('api')->user();

        return response()->json(['user' => $user]);
    }

    /**
     * 获取token的过期时间
     * @return float|int
     */
    public function tokenTTL()
    {
        return auth('api')->factory()->getTTL() * 60;
    }
}
