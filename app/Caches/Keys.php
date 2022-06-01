<?php

namespace App\Caches;

class UserKey
{
    const  LIST    = "user:list";        //用户列表
    const  INFO    = "user:info:%d";      //用户信息
    const  VISITOR = "user:visitor:%d";   //最近访问我的人
}

class OrderKey
{
    const LIST = "order:list";   //订单列表
}







