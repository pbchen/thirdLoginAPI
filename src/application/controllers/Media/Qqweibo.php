<?php

/**
 * @name IndexController
 * @author root
 * @desc 默认控制器
 * @see http://www.php.net/manual/en/class.yaf-controller-abstract.php
 */
class Media_QqweiboController extends Yaf_Controller_Abstract {

    private $_qqweiboparam;
    private $_db;
    private $_basemodel;

    /**
     * 初始化函数
     * 1、时区设置
     * 2、数据库连接配置
     */
    public function init() {

        //QQweibo的配置信息
        $this->_qqweiboparam = array(
            'media_id' => 4,
            'login_url' => '',
            'redirect_uri' => 'http://denglu.soquair.com/media_qqweibo/callback/',
            'state' => 'test'
        );
        //获取数据库连接信息
        $this->_db = Base_Db::getInstance(Yaf_Application::app()->getConfig()->application->db->toArray());
        $this->_basemodel = new BasedbModel($this->_db);
    }

    public function indexAction() {
              
        $app_id = isset($_REQUEST['app_id']) ? trim($_REQUEST['app_id']) : '69948denMVMoBujYGLSFGUHbkvP7E3';
        $mediaqqmodel = new Media_QQweiboModel($this->_basemodel,$this->_qqweiboparam);
        
        $mediaqq = $mediaqqmodel->getApp($app_id);
        if($mediaqq){
            if($mediaqq['dic_media_status']==1){

                $client_id = ($mediaqq['APPID']&&$mediaqq['APPKEY'])?$mediaqq['APPID']:$mediaqq['def_APPID'];
                $client_secret = ($mediaqq['APPID']&&$mediaqq['APPKEY'])?$mediaqq['APPKEY']:$mediaqq['def_APPKEY'];
                
                $redirect_uri =  $this->_qqweiboparam['redirect_uri'].'?app_id='.$mediaqq['app_id'];
                if($mediaqq['APPID']&&$mediaqq['APPKEY']){
                    $concat = '&';
                    if (strpos($mediaqq['receriver_url'], '?') === false) {
                        $concat = '?';
                    }
                    $redirect_uri = $mediaqq['receriver_url'] . "{$concat}url=" . urlencode($redirect_uri);
                }


                $mediaqqmodel = new QQweibo_YafQQweibo($client_id,$client_secret);
                
                $mediaqqmodel->index($redirect_uri);
            }else{
                echo '此第三方已经停用';
            }
        }else{
            echo 'error';
        }
        exit;
    }

    
    public function callbackAction() {
       
        $app_id = isset($_REQUEST['app_id']) ? trim($_REQUEST['app_id']) : '69948denMVMoBujYGLSFGUHbkvP7E3';
        $mediaqqmodel = new Media_QQweiboModel($this->_basemodel,$this->_qqweiboparam);
        
        $mediaqq = $mediaqqmodel->getApp($app_id);
        if($mediaqq){
            if($mediaqq['dic_media_status']==1){

                $client_id = ($mediaqq['APPID']&&$mediaqq['APPKEY'])?$mediaqq['APPID']:$mediaqq['def_APPID'];
                $client_secret = ($mediaqq['APPID']&&$mediaqq['APPKEY'])?$mediaqq['APPKEY']:$mediaqq['def_APPKEY'];
                
                $redirect_uri =  $this->_qqweiboparam['redirect_uri'].'?app_id='.$mediaqq['app_id'];
                if($mediaqq['APPID']&&$mediaqq['APPKEY']){
                    $concat = '&';
                    if (strpos($mediaqq['receriver_url'], '?') === false) {
                        $concat = '?';
                    }
                    $redirect_uri = $mediaqq['receriver_url'] . "{$concat}url=" . urlencode($redirect_uri);
                }


                $mediaqqmodelyaf = new QQweibo_YafQQweibo($client_id,$client_secret);
                
                
                //授权
                $rt=$mediaqqmodelyaf->shouquan($_REQUEST['code'],$_REQUEST['openid'],$_REQUEST['openkey'],$redirect_uri);
                $qqweibo_userinfo='';
                if($rt)
                {
                    $qqweibo_userinfo=$mediaqqmodelyaf->getuserinfo();
                }
                else
                {
                    echo '获取用户信息失败';
                }
                if(isset($qqweibo_userinfo['data']['openid']) && $qqweibo_userinfo['data']['openid']!='')
                {   
                    //插入`third_login`.`media_qqweibo_user`
                    $media_user_id = $mediaqqmodel->saveUser($qqweibo_userinfo['data']['openid'], $qqweibo_userinfo['data'], $mediaqq['APPID']);
                    if ($media_user_id) {
                        $usertokentime = date('Y-m-d H:i:s', time() + Yaf_Application::app()->getConfig()->application->usertokentime);
                        $login_token = $mediaqqmodel->saveAccessToken($app_id,  $media_user_id, $usertokentime);
                        if ($login_token) {
                            $url = $mediaqq['token_url'];
                            $concat = '&';
                            if (strpos($url, '?') === false) {
                                $concat = '?';
                            }
                            $url.=$concat . "token={$login_token}";
                            header("Location:$url");
                        }
                    }
                }
                else
                {
                    echo '用户信息错误';
                }
                exit;
            }else{
                echo '此第三方已经停用';
            }
        }else{
            echo 'error';
        }
        exit;
    }

}
