<?php

/**
 * Created by JetBrains PhpStorm.
 * User: zhaozhengguang
 * Date: 13-10-28
 * Time: 下午2:53
 * To change this template use File | Settings | File Templates.
 */
class Media_RenrenController extends Yaf_Controller_Abstract {

    private $_renrenparam;
    private $_db;
    private $_basemodel;

    /**
     * 初始化函数
     * 1、时区设置
     * 2、数据库连接配置
     */
    public function init() {
        
		header("Content-type: text/html; charset=utf-8");
        //Renren的配置信息
        $this->_renrenparam = array(
            'media_id' => 7,
            'redirect_uri' => 'http://denglu.soquair.com/media_renren/callback/'
        );
        //获取数据库连接信息
        $this->_db = Base_Db::getInstance(Yaf_Application::app()->getConfig()->application->db->toArray());
        $this->_basemodel = new BasedbModel($this->_db);
    }

    public function set_params($AppInfo) {
        $this->_renrenparam['APPID'] = isset($AppInfo['APPID']) && !empty($AppInfo['APPID']) ? $AppInfo['APPID'] : '';
        $this->_renrenparam['APPKEY'] = isset($AppInfo['APPKEY']) && !empty($AppInfo['APPKEY']) ? $AppInfo['APPKEY'] : '';
        $this->_renrenparam['SecretKey'] = isset($AppInfo['SecretKey']) && !empty($AppInfo['SecretKey']) ? $AppInfo['SecretKey'] : '';
    }

    public function indexAction() {
        $app_id = isset($_REQUEST['app_id']) ? trim($_REQUEST['app_id']) : '69948denMVMoBujYGLSFGUHbkvP7E3';
        $mediarenrenmodel = new Media_RenrenModel($this->_basemodel, $this->_renrenparam);

        $mediaRenren = $mediarenrenmodel->getApp($app_id);
        $AppInfo_json = ($mediaRenren['AppInfo']) ? $mediaRenren['AppInfo'] : $mediaRenren['def_AppInfo'];
        if ($mediaRenren) {
            if ($mediaRenren['dic_media_status'] == 1) {

                if (!empty($AppInfo_json)) {
                    $AppInfo = json_decode($AppInfo_json, true);
                    $this->set_params($AppInfo);
                    if (empty($this->_renrenparam['APPID']) || empty($this->_renrenparam['APPKEY']) || empty($this->_renrenparam['SecretKey'])) {
                        die('{success:false,msg:"第三方接口配置错误！"}');
                    }
                }
                $ry = new Renren_YafRenren($this->_renrenparam);
                $ry->login($app_id);
            } else {
                die('{success:false,msg:"此第三方已经停用"}');
            }
        } else {
            echo 'error';
        }
        exit;
    }

    public function callbackAction() {
        if (!isset($_REQUEST['code'])) {
            die('{success:false,msg:" code is error"}');
        }
        if (!isset($_REQUEST['app_id'])) {
            die('{success:false,msg:" app_id is error"}');
        }
        $code = $_REQUEST['code'];
        $app_id = isset($_REQUEST['app_id']) ? trim($_REQUEST['app_id']) : '69948denMVMoBujYGLSFGUHbkvP7E3';

        $mediaRenrenmodel = new Media_RenrenModel($this->_basemodel, $this->_renrenparam);
        $mediaRenren = $mediaRenrenmodel->getApp($app_id);
        $AppInfo_json = ($mediaRenren['AppInfo']) ? $mediaRenren['AppInfo'] : $mediaRenren['def_AppInfo'];

        if (!empty($AppInfo_json)) {
            $AppInfo = json_decode($AppInfo_json, true);
            $this->set_params($AppInfo);
            if (empty($this->_renrenparam['APPID']) || empty($this->_renrenparam['APPKEY']) || empty($this->_renrenparam['SecretKey'])) {
                die('{success:false,msg:"第三方接口配置错误！"}');
            }
        } else {
            die('{success:false,msg:"第三方接口配置错误！"}');
        }
        $ry = new Renren_YafRenren($this->_renrenparam);
        $token_info = $ry->getToken($code, $this->_renrenparam['redirect_uri'] . '?app_id=' . $_REQUEST['app_id']);
        if ($token_info) {
            $user = $ry->getUser();
            $user_data = array(
                'screen_name' => $user['name']
                , 'profile_image_url' => $user['avatar'][1]['url']
            );
            $media_user_id = $mediaRenrenmodel->saveUser(
                $user['id']
                , $user_data
            );
            $token_data = array(
                'screenName' => $user['name']
                , 'profileImageUrl' => $user['avatar'][1]['url']
                , 'access_token' => $token_info->accessToken
                , 'id' => $user['id']
                , 'type' => $token_info->type
                , 'refresh_token' => $token_info->refreshToken
            );
            $usertokentime = date('Y-m-d H:i:s',time() + Yaf_Application::app()->getConfig()->application->usertokentime);
            $login_token = $mediaRenrenmodel->saveAccessToken($token_data, $media_user_id, $app_id, $usertokentime);
            if ($login_token) {
                $url = $mediaRenren['token_url'];
                $concat = '&';
                if (strpos($url, '?') === false) {
                    $concat = '?';
                }
                $url .= $concat . "token={$login_token}";
                header("Location:$url");
            }
        }
        exit;
    }

}
