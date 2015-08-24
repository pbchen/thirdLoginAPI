<?php

/**
 * @name IndexController
 * @author root
 * @desc 默认控制器
 * @see http://www.php.net/manual/en/class.yaf-controller-abstract.php
 */
class Media_WeiboController extends Yaf_Controller_Abstract {

    private $_weiboparam;
    private $_db;
    private $_basemodel;

    /**
     * 初始化函数
     * 1、时区设置
     * 2、数据库连接配置
     */
    public function init() {

        //QQ的配置信息
        $this->_weiboparam = array(
            'media_id' => 3,
            'redirect_uri' => 'http://denglu.soquair.com/media_weibo/callback/'
        );
        //获取数据库连接信息
        $this->_db = Base_Db::getInstance(Yaf_Application::app()->getConfig()->application->db->toArray());
        $this->_basemodel = new BasedbModel($this->_db);
    }

    public function indexAction() {
        
        $app_id = isset($_REQUEST['app_id']) ? trim($_REQUEST['app_id']) : '69948denMVMoBujYGLSFGUHbkvP7E3';
        $mediaweibomodel = new Media_WeiboModel($this->_basemodel,$this->_weiboparam);
        $mediaweibo = $mediaweibomodel->getApp($app_id);
        if($mediaweibo){
            if($mediaweibo['dic_media_status']==1){
                $WB_AKEY = ($mediaweibo['APPID']&&$mediaweibo['APPKEY'])?$mediaweibo['APPID']:$mediaweibo['def_APPID'];
                $WB_SKEY = ($mediaweibo['APPID']&&$mediaweibo['APPKEY'])?$mediaweibo['APPKEY']:$mediaweibo['def_APPKEY'];
                $redirect_uri =  $this->_weiboparam['redirect_uri'].'?app_id='.$mediaweibo['app_id'];
                if($mediaweibo['APPID']&&$mediaweibo['APPKEY']){
                    $concat = '&';
                    if (strpos($mediaweibo['receriver_url'], '?') === false) {
                        $concat = '?';
                    }
                    $redirect_uri = $mediaweibo['receriver_url'] . "{$concat}url=" . urlencode($redirect_uri);
                }
                $weiboapi = new Weibo_YafWeibo(array('WB_AKEY'=>$WB_AKEY,'WB_SKEY'=>$WB_SKEY));
                $weiboapi->login($redirect_uri);

            }else{
                echo '此第三方已经停用';
            }
        }else{
            echo 'error';
        }
        exit;
    }

    public function callbackAction() {
        $mediaweibomodel = new Media_WeiboModel($this->_basemodel,$this->_weiboparam);
        
        $app_id = isset($_REQUEST['app_id']) ? trim($_REQUEST['app_id']) : '69948denMVMoBujYGLSFGUHbkvP7E3';
        $mediaweibo = $mediaweibomodel->getApp($app_id);
        $WB_AKEY = ($mediaweibo['APPID']&&$mediaweibo['APPKEY'])?$mediaweibo['APPID']:$mediaweibo['def_APPID'];
        $WB_SKEY = ($mediaweibo['APPID'] && $mediaweibo['APPKEY']) ? $mediaweibo['APPKEY'] : $mediaweibo['def_APPKEY'];
        $redirect_uri = $this->_weiboparam['redirect_uri'] . '?app_id=' . $mediaweibo['app_id'];
        
        if ($mediaweibo['APPID'] && $mediaweibo['APPKEY']) {
            $concat = '&';
            if (strpos($mediaweibo['receriver_url'], '?') === false) {
                $concat = '?';
            }
            $redirect_uri = $mediaweibo['receriver_url'] . "{$concat}url=" . urlencode($redirect_uri);
        }
        $weiboapi = new Weibo_YafWeibo(array('WB_AKEY' => $WB_AKEY, 'WB_SKEY' => $WB_SKEY));
        $code = isset($_REQUEST['code']) ? $_REQUEST['code'] : '';
        $token = $weiboapi->getToken($code, $redirect_uri);
        $weiboUser= NULL;
        if($token&&$token['access_token']){
            $access_token = $token['access_token'];
            $weiboUser = $weiboapi->getUser($access_token);
        }

        if(!$weiboUser||!isset($weiboUser['screen_name'])){
            /**
             * 为了审核
             */
            $weiboUser=array(
                'screen_name'=>$token['uid'],
                'profile_image_url'=>'',
            );
        }
        if($weiboUser&&$weiboUser['screen_name']){
            $media_user_id = $mediaweibomodel->saveUser($token['uid'], $weiboUser);   
    
            if($media_user_id){
                $usertokentime = date('Y-m-d H:i:s',time() + Yaf_Application::app()->getConfig()->application->usertokentime);
                $login_token = $mediaweibomodel->saveAccessToken($app_id,$access_token,$token['uid'],$media_user_id,$usertokentime);
                if($login_token){
                    $url=$mediaweibo['token_url'];
                    $concat = '&';
                    if (strpos($url, '?') === false) {
                        $concat = '?';
                    }
                    $url.=$concat."token={$login_token}";
                    header("Location:$url");
                }
            }
        }
        else{
            header("Content-type: text/html; charset=utf-8");
            echo "<br>对不起！获取用户信息失败，可能正在审核中";
        }
        exit;
    }
}
