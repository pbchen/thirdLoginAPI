<?php

class Base_OpenSSL {

    /**
     * 单例模式实例化对象
     *
     * @var object
     */
    public static $instance;
    private $pripath;
    private $pubpath;
    private $pri;
    private $pub;

    /* function __construct($pripath,$pubpath)
      {
      $this->pripath=$pripath;
      $this->pubpath=$pubpath;
      }
     */

    function __construct($pri, $pub) {
        $this->pri = $pri;
        $this->pub = $pub;
    }

    public function createKey() {
        $pri = '';
        $res = openssl_pkey_new();
        openssl_pkey_export($res, $pri);
        $d = openssl_pkey_get_details($res);
        $pub = $d['key'];
        return array('pri' => $pri, 'pub' => $pub);
        /*
          $fp = fopen($this->pripath,'w');
          flock($fp,LOCK_EX|LOCK_NB);
          fwrite($fp,$pri);
          flock($fp,LOCK_UN);
          fclose($fp);
          $fp = fopen($this->pubpath,'w');
          flock($fp,LOCK_EX|LOCK_NB);
          fwrite($fp,$pub);
          flock($fp,LOCK_UN);
          fclose($fp);
          return true;
         */
    }

    /**
     * 数组签名
     * 将数据的所有值安装a-z排序组成ka=va&kb=vb的信息进行签名如果有sign的key除外
     * @param array $arr
     */
    public function sign_arr($arr) {
        $body = '';
        if (is_array($arr)) {
            ksort($arr);
            $cat = '';
            foreach ($arr as $k => $v) {
                if ("sign" != $k) {
                    $body .=$cat . $k . "=" . $v;
                    $cat = '&';
                }
            }
        }
        $pri = $this->pri;
        $res = openssl_pkey_get_private($pri);
        if (openssl_sign($body, $out, $res)) {
            return base64_encode($out);
        }
        return '';
    }

    public function sign($body) {
        /* $file = fopen($this->pripath,"r");
          $pri = fread($file,filesize($this->pripath));
          fclose($file); */
        $pri = $this->pri;
        $res = openssl_pkey_get_private($pri);
        if (openssl_sign($body, $out, $res)) {
            return base64_encode($out);
        }
        return '';
    }

    public function verify($body, $sig) {
        /* $file = fopen($this->pubpath,"r");
          $pub = fread($file,filesize($this->pubpath));
          fclose($file); */
        $pub = $this->pub;
        $sig = base64_decode($sig);
        $res = openssl_pkey_get_public($pub);
        if (openssl_verify($body, $sig, $res) === 1) {
            return true;
        }
        return false;
    }

    public static function getInstance($pri, $pub) {

        if (!self::$instance) {
            self::$instance = new self($pri, $pub);
        }

        return self::$instance;
    }

}

?>