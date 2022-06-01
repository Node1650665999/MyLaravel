<?php
namespace App\Caches;

class BaseCache {

    /**
     * 格式化key
     * @param $key
     * @param ...$param
     * @return string
     */
    function formatKey($key, ... $param) : string
    {
        if (! count($param)) return $key;
        return sprintf($key, ... $param);
    }

}



