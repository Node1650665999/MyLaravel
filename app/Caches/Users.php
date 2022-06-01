<?php

namespace App\Caches;

class Users extends BaseCache
{
    function Info($uid)
    {
        $key = $this->formatKey(UserKey::INFO, $uid);
        //todo
    }

}