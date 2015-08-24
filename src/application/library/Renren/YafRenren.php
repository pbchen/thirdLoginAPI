<?php
include_once (dirname(__FILE__).'/rennclient/RennClient.php');
/**
 * Created by JetBrains PhpStorm.
 * User: zhaozhengguang
 * Date: 13-10-28
 * Time: 下午3:40
 * To change this template use File | Settings | File Templates.
 */
class Renren_YafRenren {
    private $_o;
    private $_params;

    /**
     * @param $params
     */
    function __construct($params) {
        $this->_params = $params;
        $this->_o = new RennClient( $params['APPKEY'],  $params['SecretKey'] );;
    }

    /**
     * 登陆
     * @param $app_id
     */
    public function login( $app_id ){
        $state = uniqid ( 'renren_', true );
        $code_url = $this->_o->getAuthorizeURL( $this->_params['redirect_uri']."?app_id=".$app_id , 'code',$state);
        header("Location:$code_url");
    }

    /**
     * 获取token
     * @param $code
     * @param $callbackurl
     * @return array
     */
    public function getToken($code,$callbackurl){
        //*****************需定义参数数组
        try {
            $token = $this->_o->getTokenFromTokenEndpoint('code',array('code'=>$code, 'redirect_uri'=>$callbackurl),'bearer');
        } catch (OAuthException $e) {
        }
        return $token;
    }

    /**
     * 获取用户信息
     * @return User
     */
    public function getUser(){
        $user_service = $this->_o->getUserService();
        $user = $user_service->getUserLogin ();
        return $user;
    }
}