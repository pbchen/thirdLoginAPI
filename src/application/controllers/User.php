<?php

/**
 * @name IndexController
 * @author root
 * @desc 默认控制器
 * @see http://www.php.net/manual/en/class.yaf-controller-abstract.php
 */
class UserController extends Yaf_Controller_Abstract {

    private $_db;
    private $_basemodel;

    /**
     * 初始化函数
     * 1、时区设置
     * 2、数据库连接配置
     */
    public function init() {
        header("Content-type: application/json; charset=utf-8");
        ini_set('date.timezone', 'Asia/Shanghai');
        //获取数据库连接信息
        $this->_db = Base_Db::getInstance(Yaf_Application::app()->getConfig()->application->db->toArray());
        $this->_basemodel = new BasedbModel($this->_db);
    }

    public function getAction() {

        $appid = isset($_REQUEST['appid']) ? trim($_REQUEST['appid']) : '';
        $token = isset($_REQUEST['token']) ? trim($_REQUEST['token']) : '';
        $timestamp = isset($_REQUEST['timestamp']) ? trim($_REQUEST['timestamp']) : '';
        $version = isset($_REQUEST['version']) ? trim($_REQUEST['version']) : '';
        $sign_type = isset($_REQUEST['sign_type']) ? trim($_REQUEST['sign_type']) : 'md5';
        $sign = isset($_REQUEST['sign']) ? trim($_REQUEST['sign']) : '';
        
        $sing_arr = array(
            'appid' => $appid,
            'token' => $token,
            'timestamp' => $timestamp,
            'version' => $version,
            'sign_type' => $sign_type
        );
        ksort($sing_arr);
        $concat = '';
        $signp = '';
        foreach ($sing_arr as $key => $value) {
            $signp = $signp . $key . '=' . urlencode($value);
            $concat = '&';
        }
        $usermodel = new UserModel($this->_basemodel);
        $userapp = $usermodel->getApp($appid);
        $mediauser = NULL;
        if ($userapp) {
            if ($sign == md5($signp . $userapp['app_key'])) {
                $mediauser = $usermodel->getMedia_user($appid, $token);
            } else {
                echo 'sign error';
            }
        }

        echo json_encode($mediauser);

        exit;
    }
}
    