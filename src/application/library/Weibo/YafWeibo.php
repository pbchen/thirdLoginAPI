<?php
include_once(dirname(__FILE__). '/saetv2.ex.class.php' );
class Weibo_YafWeibo { 
    private $_o;
    private $_params;

    /**
     * 连接数据库
     *
     * @param string $host
     * @param string $username
     * @param string $password
     * @param string $port
     * @param string $character
     * @param string $dbname
     */
    function __construct($params) {
        $this->_params = $params;
       $this->_o = new SaeTOAuthV2( $this->_params['WB_AKEY'] , $this->_params['WB_SKEY'] );
    }
    /**
     * 登陸
     * @param type $callbackurl 囘調URL
     */
    public function login($callbackurl){
        $code_url = $this->_o->getAuthorizeURL( $callbackurl );
//        return $code_url;
        header("Location:$code_url");
    }
    /**
     * 獲取登錄的token
     * @param type $code
     * @param type $callbackurl
     * @return type
     * Array ( [access_token] => 2.008HNzGELo1hVC588df86136oEE6FE [remind_in] => 157679999 [expires_in] => 157679999 [uid] => 3767777785 )
     */
    public function getToken($code,$callbackurl){
        $token=FALSE;
        $keys = array();
	$keys['code'] = $code;
	$keys['redirect_uri'] = $callbackurl;
	try {
		$token = $this->_o->getAccessToken( 'code', $keys ) ;
	} catch (OAuthException $e) {
	}
        return $token;
    }
    /**
     * 獲取用戶信息
     * @param type $access_token
     * @return type
     * Array ( [id] => 3767777785 [idstr] => 3767777785 [class] => 1 
     * [screen_name] => soqugame [name] => soqugame 
     * [province] => 11 [city] => 1 [location] => 北京 东城区 [description] => [url] => 
     * [profile_image_url] => http://tp2.sinaimg.cn/3767777785/50/0/1 
     * [profile_url] => u/3767777785 [domain] => [weihao] => [gender] => m [followers_count] => 3 [friends_count] => 18 [statuses_count] => 0 [favourites_count] => 0 [created_at] => Tue Sep 03 16:48:05 +0800 2013 [following] => [allow_all_act_msg] => [geo_enabled] => 1 [verified] => 1 [verified_type] => 2 [remark] => [ptype] => 0 [allow_all_comment] => 1 [avatar_large] => http://tp2.sinaimg.cn/3767777785/180/0/1 [avatar_hd] => http://tp2.sinaimg.cn/3767777785/180/0/1 [verified_reason] => 上海搜趣广告有限公司 [follow_me] => [online_status] => 0 [bi_followers_count] => 0 [lang] => zh-cn [star] => 0 [mbtype] => 0 [mbrank] => 0 [block_word] => 0 )
     */
    public function getUser($access_token){
        $c = new SaeTClientV2( $this->_params['WB_AKEY'] , $this->_params['WB_SKEY'] , $access_token );
        $ms  = $c->home_timeline(); // done
        $uid_get = $c->get_uid();
        $uid = $uid_get['uid'];

        $user_message = $c->show_user_by_id( $uid);//根据ID获取用户等基本信息
        return $user_message;
    }
}
