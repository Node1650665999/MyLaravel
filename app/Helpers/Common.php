<?php

use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;

defined('WEB_PATH') or define('WEB_PATH', php_sapi_name() == 'cli' ? '' : 'http://' . $_SERVER['HTTP_HOST']);
defined('__CURRENT__') or define('__CURRENT__', date('Y-m-d H:i:s'));

/**
 * @return string
 */
function getClientIp(): string
{
    $ip = "unknown";
    if (isset($_SERVER)) {
        if (isset($_SERVER["HTTP_X_FORWARDED_FOR"])) {
            $ip = $_SERVER["HTTP_X_FORWARDED_FOR"];
        } elseif (isset($_SERVER["HTTP_CLIENT_ip"])) {
            $ip = $_SERVER["HTTP_CLIENT_ip"];
        } else {
            $ip = $_SERVER["REMOTE_ADDR"];
        }
    } else {
        if (getenv('HTTP_X_FORWARDED_FOR')) {
            $ip = getenv('HTTP_X_FORWARDED_FOR');
        } elseif (getenv('HTTP_CLIENT_ip')) {
            $ip = getenv('HTTP_CLIENT_ip');
        } else {
            $ip = getenv('REMOTE_ADDR');
        }
    }
    if (trim($ip) == "::1") {
        $ip = "127.0.0.1";
    }
    return $ip;
}


/**
 * josn_encode不对中文进行转码
 * @param $arr
 * @return false|string
 */
function toJsonUnicode($arr)
{
    if (is_array($arr)) {
        return json_encode($arr, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
    return $arr;
}

/**
 * 验证是否中国手机号
 * @param $mobile
 * @return bool
 */
function isChinaMobile($mobile): bool
{
    $patten = '/^(13[0-9]|14[0-9]|16[0-9]|17[0-9]|15[0-9]|18[0-9]|19[0-9])\d{8}$/';
    if (preg_match($patten, $mobile)) {
        return true;
    } else {
        return false;
    }
}

/**
 * 是否为二维数组
 * @param $arr
 * @return bool
 */
function isTwoDimensional($arr): bool
{
    if (count($arr) == count($arr, 1)) {
        return false;
    } else {
        return true;
    }
}

/**
 * @param null $time
 * @return string
 */
function formatDate($time = NULL): string
{
    $text = '';
    $time = $time === NULL || $time > time() ? time() : intval($time);
    $t = time() - $time; //时间差 （秒）
    $y = date('Y', $time) - date('Y', time());//是否跨年
    switch ($t) {
        case $t == 0:
            $text = '刚刚';
            break;
        case $t < 60:
            $text = $t . '秒前'; // 一分钟内
            break;
        case $t < 60 * 60:
            $text = floor($t / 60) . '分钟前'; //一小时内
            break;
        case $t < 60 * 60 * 24:
            $text = floor($t / (60 * 60)) . '小时前'; // 一天内
            break;
        case $t < 60 * 60 * 24 * 3:
            $text = floor($time / (60 * 60 * 24)) == 1 ? '昨天 ' . date('H:i', $time) : '前天 ' . date('H:i', $time); //昨天和前天
            break;
        case $t < 60 * 60 * 24 * 30:
            $text = date('m月d日 H:i', $time); //一个月内
            break;
        case $t < 60 * 60 * 24 * 365 && $y == 0:
            $text = date('m月d日', $time); //一年内
            break;
        default:
            $text = date('Y年m月d日', $time); //一年以前
            break;
    }
    return $text;
}

/**
 * 计算两个时间差值
 * @param $time1
 * @param $time2
 * @return array
 */
function diffTime($time1, $time2)
{
    $timediff = abs($time2 - $time1);

    //计算天数
    $days = intval($timediff / 86400);

    //计算小时数
    $remain = $timediff % 86400;
    $hours = intval($remain / 3600);

    //计算分钟数
    $remain = $remain % 3600;
    $mins = intval($remain / 60);

    //计算秒数
    $secs = $remain % 60;

    return [
        "days"          => $days,
        "hours"         => $hours,
        "minutes"       => $mins,
        "seconds"       => $secs
    ];
}


/**
 * 去除空
 * @param $str
 * @return string
 */
function trimall($str): string
{
    $qian = array(" ", "　", "\t", "\n", "\r");
    $hou = array("", "", "", "", "");
    return str_replace($qian, $hou, $str);
}


/**
 * 判断是否有效的 http 地址
 * @param $url
 * @return false|int
 */
function isUrl($url)
{
    $preg = "/^http(s)?:\\/\\/.+/";
    return preg_match($preg, $url);
}

/**
 * 版本判断app版本是否大于某个版本
 * @param $version
 * @param string $operator
 * @return bool|int
 */
function appVersionGreaterThan($version, $operator = '>')
{
    $appVersion = input('app_version');
    return version_compare($appVersion, $version, $operator);
}

/**
 * 处理非法的视频地址
 * @param $url
 * @param string $need
 * @return string
 */
function handleInValidVideoUrl($url, $need = 'video')
{
    //非合法的url
    if (!filter_var($url, FILTER_VALIDATE_URL) || !$url) {
        return '';
    }

    $headers = get_headers($url, 1);
    $contentType = $headers['Content-Type'];

    //非指定类型
    if (strstr($contentType, $need) == false) {
        return '';
    }

    return $url;
}

/**
 * 获取视频帧
 * @param $videoUrl
 * @param $offset
 * @param string $coverUrl
 * @return string
 */
function getVideoFrame($videoUrl, $offset, $coverUrl = '')
{
    if ($coverUrl) {
        return $coverUrl;
    }

    return rtrim($videoUrl, '?') . "?vframe/jpg/offset/{$offset}";
}

/**
 * 货币金额计算
 * @param $n1 第一个数
 * @param $symbol 计算符号 + - * / %
 * @param $n2 第二个数
 * @param string $scale 精度 默认为小数点后两位
 * @return  string
 */
function priceCalc($n1, $symbol, $n2, $scale = '2')
{
    $res = "";
    switch ($symbol) {
        case "+"://加法
            $res = bcadd($n1, $n2, $scale);
            break;
        case "-"://减法
            $res = bcsub($n1, $n2, $scale);
            break;
        case "*"://乘法
            $res = bcmul($n1, $n2, $scale);
            break;
        case "/"://除法
            $res = bcdiv($n1, $n2, $scale);
            break;
        case "%"://求余、取模
            $res = bcmod($n1, $n2, $scale);
            break;
        default:
            $res = "";
            break;
    }
    return $res ? $res * 1 : '';
}

/**
 * 以日志滚动的方式记录日志
 * @param $log
 * @param $file
 */
function logRotating($log, $file)
{
    // 大于5M重命名
    if (is_file($file) && filesize($file) > 5 * 1024 * 1024) {
        rename($file, $file . '_' . date('YmdHis'));
    }

    $log = is_array($log) ? toJsonUnicode($log) : $log;

    //日志滚动默认保存七天
    (new Logger('log'))->pushHandler(new RotatingFileHandler($file, 7))
        ->info($log);
}

/**
 * 记录日志到单个文件
 * @param $log
 * @param $file
 */
function logSingle($log, $file)
{
    // 大于5M重命名
    if (is_file($file) && filesize($file) > 5 * 1024 * 1024) {
        rename($file, $file . '_' . date('YmdHis'));
    }

    $log = is_array($log) ? toJsonUnicode($log) : $log;

    (new Logger('single'))->pushHandler(new StreamHandler($file))->info($log);
}


/**
 * 日志记录
 * @param string $data
 * @param null $selfFilePath
 */
function writeLog($data = '记录日志', $selfFilePath = null)
{
    $trace = debug_backtrace(false);
    $request = request();
    $url = $request->url();
    $param = $request->all();
    $input =  file_get_contents("php://input") ?: ($param ? toJsonUnicode($param) : '');
    $formatParam = sprintf("%s", $input);
    $path = preg_replace("/\\\+/", "/", $trace[1]['class']);
    $filename = $trace[1]['function'];
    $line = $trace[0]['line'];

    $data = is_array($data) ? toJsonUnicode($data) : $data;
    $filePath = $selfFilePath ? $selfFilePath : storage_path('logs/' . trim($path, '/') . "/{$filename}.log");
    $log = "data:{$data}" . PHP_EOL . "url:{$url}" . PHP_EOL . "param:{$formatParam}" . PHP_EOL . "line:{$line}";

    logRotating($log, $filePath);
}

/**
 * 提取二维数组的某一列做为Key
 * @param $list
 * @param $key
 * @return array
 */
function setIndexKey($list, $key)
{
    return $list ? array_column($list, null, $key) : [];
}

/**
 * 非中文替换
 * @param $word
 * @return bool|string
 */
function filterInvalidChinese($word)
{
    $word = preg_replace('/[^\x{4e00}-\x{9fa5}]/u', '', $word);
    return $word;
}

/**
 * 非中文替换
 * @param $value
 * @return bool|string
 */
function checkWords($value)
{
    $value = preg_replace('/[^\x{4e00}-\x{9fa5}]/u', '', $value);
    if (mb_strlen($value, 'utf-8') != 5) {
        return '汉字字数必须为5个';
    }

    return true;
}


/**
 * 参数响应
 * @param int $code
 * @param string $msg
 * @param array $data
 * @return \Illuminate\Http\JsonResponse
 */
function apiResponse($code = 200, $msg = '成功', $data = [])
{
    $data = [
        'code' => $code,
        'msg' => $msg,
        'data' => $data ?: null,
    ];
    return response()->json($data);
}


if (!function_exists('dd')) {
    /**
     * @param mixed ...$vars
     */
    function dd(...$vars)
    {
        echo '<pre>';
        foreach ($vars as $v) {
            print_r($v);
            echo '<br>';
        }
        exit(1);
    }
}

/**
 * @param $words
 * @return string
 */
function handleWords($words)
{
    $words = preg_replace('/[^\x{4e00}-\x{9fa5}]/u', '', $words);
    $words = array_filter(mb_str_split($words));
    return implode(',', $words);
}





