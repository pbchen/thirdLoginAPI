<?php
//include_once(dirname(__FILE__). '/saetv2.ex.class.php' );
//require_once './Config.php';

include_once(dirname(__FILE__). '/Tencent.php' );
class QQweibo_YafQQweibo { 
    private $client_id;
    private $client_secret;
    private $debug;
    function __construct($client_id,$client_secret) {
        
       $this->client_id = $client_id;
       $this->client_secret = $client_secret;
       $this->debug=false;
       //打开session
        if (!isset($_SESSION)) {
            session_start();
        }
    }
    /**
     * 登陸
     * @param type $callbackurl 囘調URL
     */
    public function index($callback){
       
        OAuth::init($this->client_id, $this->client_secret);
        Tencent::$debug = $this->debug;
        
        header('Content-Type: text/html; charset=utf-8');
        
        $url = OAuth::getAuthorizeURL($callback);
       
        header('Location: ' . $url);
        exit;
        
    }
    //授权
    public function shouquan($code,$openid,$openkey,$redirect_uri)
    {
        OAuth::init($this->client_id, $this->client_secret);
        Tencent::$debug = $this->debug;
        
        
        
        $callback = $redirect_uri;//回调url
        if ($code) {//已获得code
            
            //获取授权token
            $url = OAuth::getAccessToken($code, $callback);
            
            $r = Http::request($url);
            
            parse_str($r, $out);
            
            //存储授权数据
            if (@$out['access_token']) {
                
                $_SESSION['t_access_token'] = $out['access_token'];
                $_SESSION['t_refresh_token'] = $out['refresh_token'];
                $_SESSION['t_expire_in'] = $out['expires_in'];
                $_SESSION['t_code'] = $code;
                $_SESSION['t_openid'] = $openid;
                $_SESSION['t_openkey'] = $openkey;

                //验证授权
                $r = OAuth::checkOAuthValid();
                
                if ($r) {
                    //echo('<h3>授权成功!!!</h3><br>');
                    //print_r($r);exit;
                    //header('Location: ' . $callback);//刷新页面
                    return $r;
                } else {
                    exit('<h3>授权失败,请重试</h3>');
                }
            } else {
                exit($r);
            }
        } 
    }
    
    //获取用户信息
    public function getuserinfo()
    {
        if ($_SESSION['t_access_token'] || ($_SESSION['t_openid'] && $_SESSION['t_openkey'])) {//用户已授权
            //echo '<pre><h3>已授权</h3>用户信息：<br>';
            //获取用户信息
            $r = Tencent::api('user/info');
            return json_decode($r, true);
            
        }
    }
}
