<?php

class Base_Ctrl {

    public static function getclientip() {
        if (getenv('HTTP_CLIENT_IP') && strcasecmp(getenv('HTTP_CLIENT_IP'), 'unknown')) {
            $CLIENT_IP = getenv('HTTP_CLIENT_IP');
        } elseif (getenv('HTTP_X_FORWARDED_FOR') && strcasecmp(getenv('HTTP_X_FORWARDED_FOR'), 'unknown')) {
            $CLIENT_IP = getenv('HTTP_X_FORWARDED_FOR');
        } elseif (getenv('REMOTE_ADDR') && strcasecmp(getenv('REMOTE_ADDR'), 'unknown')) {
            $CLIENT_IP = getenv('REMOTE_ADDR');
        } elseif (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], 'unknown')) {
            $CLIENT_IP = $_SERVER['REMOTE_ADDR'];
        }
        return $CLIENT_IP;
    }

    public static function chinese_strlen($str, $charset = 'utf8') {
        if (strtolower($charset) == 'utf8')
            $str = iconv('utf-8', 'gb2312', $str);
        $len = strlen($str);
        $i = 0;
        $j = 0;
        while ($i < $len) {
            if (preg_match("/^[" . chr(0xa1) . "-" . chr(0xff) . "]+$/", $str[$i])) {
                $i+=2;
            } else {
                $i+=1;
            }
            $j+=1;
        }
        return $j;
    }

    /**
     * 判断大小写字母和数字下划线
     * @param type $str
     * @param type $mim
     * @param type $max
     * @return type
     */
    public static function is_User($str,$mim=-1,$max=-1) {
        if($mim>=0){
            if(strlen($str)<$mim){
                return FALSE;
            }
        }elseif($max>=0){
            if(strlen($str)>$max){
                return FALSE;
            }
        }
        return preg_match("/^[A-Za-z0-9\_\-\.]+$/", $str);
    }

    /**
     * 邮箱格式检测
     *
     * @param string $email
     * @return bool
     */
    public static function is_Email($email) {
        //return preg_match("/^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/", $email);
        //return preg_match("/^[_.0-9a-z-]+@([0-9a-z][0-9a-z-]+.)+[a-z]{2,3}$/",$email);
        return preg_match("/^[\w_-]+(\.[-_\w]+)*@[\w_-]+(\.[-_\w]+)*\.[\w_-]+$/", $email);
    }

    public static function is_Num($str) {
        return preg_match("/^[0-9]+$/", $str);
    }

    public static function is_Space($str) {
        if (strpos($str, ' ') || is_int(strpos($str, ' '))) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * 手机格式检测
     *
     * @param string $email
     * @return bool
     */
    public static function is_Phone($phone) {
        return preg_match("/1[0-9]{10}/", $phone);
    }

    /**
     * 建立完整目录
     *
     * @param string $pathString
     * @return string/boolean
     */
    public static function makeDir($pathString) {
        $pathArray = explode('/', $pathString);
        $tmpPath = array_shift($pathArray);
        foreach ($pathArray as $val) {
            $tmpPath .= "/" . $val;
            if (!is_dir($tmpPath))
                @mkdir($tmpPath, 0777);
        }
        if (is_dir($tmpPath)) {
            return $tmpPath;
        } else {
            return false;
        }
    }

    public static function createLog($name, $CLASS, $function, $str) {
        $james = fopen($logpath = Yaf_Application::app()->getConfig()->application->logpath . '/' . date("Y-m-d") . '/' . $name, "a");
        fwrite($james, date('Y-m-d H:i:s') . '   ' . $CLASS . '->' . $function . '   ' . $str . chr(10));
        fclose($james);
    }

    public static function create_guid() {
        $charid = strtoupper(md5(uniqid(mt_rand(), true)));
        $hyphen = chr(45); // "-";
        $uuid =
                substr($charid, 0, 8) . $hyphen
                . substr($charid, 8, 4) . $hyphen
                . substr($charid, 12, 4) . $hyphen
                . substr($charid, 16, 4) . $hyphen
                . substr($charid, 20, 12);
        return $uuid;
    }

    /**
     * 数组转换成字符串
     * Enter description here ...
     * @param array $arr 需要转换数组
     * @param array $removekey 需要排除字段
     * @param int $sort 排序1正序、0不排序、-1倒序
     * @param bool $encode 是否编码
     * @param string $equal 连接符
     * @param string $split 分割符
     */
    public static function arrtostr($arr, $removekey = array(), $sort = 1, $encode = false, $equal = '=', $split = '&') {
        $str = '';
        if (is_array($arr)) {
            if ($sort == 1) {
                ksort($arr);
            } elseif ($sort == -1) {
                krsort($arr);
            }
            $cat = '';
            foreach ($arr as $k => $v) {
                if (!in_array($k, $removekey)) {
                    $str .=$cat . $k . $equal . $v;
                    $cat = $split;
                }
            }
        }
        return $str;
    }

}