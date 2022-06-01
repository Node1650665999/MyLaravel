<?php

namespace App\ErrorCode;

class Codes
{
    static $businessErrorCode     = 4000;
    static $appNotExistCode       = 4001;
    static $signErrorCode         = 4002;
    static $appExpireCode         = 4003;
    static $tokenErrorCode        = 4004;
    static $accessTokenErrorCode  = 4444;
    static $systemErrorCode       = 5000;

    static function msg($code)
    {
        $textMap = [
            static::$businessErrorCode      => "业务错误码",
            static::$appNotExistCode        => "appid不存在",
            static::$signErrorCode          => "签名错误",
            static::$appExpireCode          => "合作期已到",
            static::$tokenErrorCode         => "token错误",
            static::$accessTokenErrorCode   => "access_token已失效",
            static::$systemErrorCode        => "系统错误"
        ];

        return $textMap[$code];
    }


}
