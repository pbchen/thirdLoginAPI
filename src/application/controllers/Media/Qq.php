<?php

/**
 * @name IndexController
 * @author root
 * @desc 默认控制器
 * @see http://www.php.net/manual/en/class.yaf-controller-abstract.php
 */
class Media_QqController extends Yaf_Controller_Abstract {

    private $_qqparam;
    private $_db;
    private $_basemodel;

    /**
     * 初始化函数
     * 1、时区设置
     * 2、数据库连接配置
     */
    public function init() {

        //QQ的配置信息
        $this->_qqparam = array(
            'media_id' => 13,
            'login_url' => 'https://graph.qq.com/oauth2.0/authorize?response_type=code',
            'redirect_uri' => 'http://denglu.soquair.com/media_qq/callback/',
            'state' => 'test'
        );
        //获取数据库连接信息
        $this->_db = Base_Db::getInstance(Yaf_Application::app()->getConfig()->application->db->toArray());
        $this->_basemodel = new BasedbModel($this->_db);
    }

    public function indexAction() {
        $app_id = isset($_REQUEST['app_id']) ? trim($_REQUEST['app_id']) : '69948denMVMoBujYGLSFGUHbkvP7E3';
        $mediaqqmodel = new Media_QQModel($this->_basemodel,$this->_qqparam);
        $mediaqq = $mediaqqmodel->getApp($app_id);
        if($mediaqq){
            if($mediaqq['dic_media_status']==1){

                $client_id = ($mediaqq['APPID']&&$mediaqq['APPKEY'])?$mediaqq['APPID']:$mediaqq['def_APPID'];
                $redirect_uri =  $this->_qqparam['redirect_uri'].'?app_id='.$mediaqq['app_id'];
                $state = md5($mediaqq['app_key']);

                $redirect_uri = urlencode($redirect_uri);

                $qcinit=new QQAPI_YafQQConnetAPI();
                $qc = new QC();
                $qc->qq_login($client_id,$redirect_uri,$mediaqq['app_id'],$state);
            }else{
                echo '此第三方已经停用';
            }
        }else{
            echo 'error';
        }
        exit;
    }

    public function callbackAction() {
        $mediaqqmodel = new Media_QQModel($this->_basemodel,$this->_qqparam);
        $app_id = isset($_REQUEST['app_id']) ? trim($_REQUEST['app_id']) : '69948denMVMoBujYGLSFGUHbkvP7E3';
        $mediaqq = $mediaqqmodel->getApp($app_id);
        $APPID = ($mediaqq['APPID']&&$mediaqq['APPKEY'])?$mediaqq['APPID']:$mediaqq['def_APPID'];
        $APPKEY = ($mediaqq['APPID']&&$mediaqq['APPKEY'])?$mediaqq['APPKEY']:$mediaqq['def_APPKEY'];
        $redirect_uri =  $this->_qqparam['redirect_uri'].'?app_id='.$mediaqq['app_id'];
        $state = md5($mediaqq['app_key']);
        
        $qcinit=new QQAPI_YafQQConnetAPI();
        $qc = new QC();
        $access_token = $qc->qq_callback($APPID,$redirect_uri,$APPKEY);
        echo $access_token.'----------';
        $openid =  $qc->get_openid($access_token);
        echo $openid.'---------';

        
        $qqUser = $qc->my_get_userinfo($access_token,$openid,$APPID);

        if($qqUser&&$qqUser->nickname){
            $media_user_id = $mediaqqmodel->saveUser($openid, $qqUser,$APPID);      
            if($media_user_id){
                $usertokentime = date('Y-m-d H:i:s',time() + Yaf_Application::app()->getConfig()->application->usertokentime);
                $login_token = $mediaqqmodel->saveAccessToken($app_id,$access_token,$openid,$media_user_id,$usertokentime);
                if($login_token){
                    $url=$mediaqq['token_url'];
                    $concat = '&';
                    if (strpos($url, '?') === false) {
                        $concat = '?';
                    }
                    $url.=$concat."token={$login_token}";
                    header("Location:$url");
                }
            }
        }
        exit;
    }

}
