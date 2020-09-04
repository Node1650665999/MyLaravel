<?php

/**
 * 是否为二维数组
 * @param $arr
 * @return bool
 */
function isTwoDimensional($arr)
{
    if (count($arr) == count($arr, 1))
    {
        return false;
    }
    else
    {
        return true;
    }
}

/**
 * 当前时间
 * @return false|string
 */
function currentTime()
{
    return date('Y-m-d H:i:s');
}

