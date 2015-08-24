<?php
/**
 * @name IndexController
 * @author root
 * @desc 默认控制器
 * @see http://www.php.net/manual/en/class.yaf-controller-abstract.php
 */
class Media_QihooController extends Yaf_Controller_Abstract{
	private $_qihooparam;
	private $_db;
	private $_basemodel;
	
	/**
	 * 初始化函数
	 * 1、时区设置
	 * 2、数据库连接配置
	 */
	public function init() {

		//360配置信息
		$this->_qihooparam = array(
				'media_id' => 23,
				'redirect_uri' => 'http://denglu.soquair.com/Media_Qihoo/callback',
		);
		//获取数据库连接信息
		$this->_db = Base_Db::getInstance(Yaf_Application::app()->getConfig()->application->db->toArray());
		$this->_basemodel = new BasedbModel($this->_db);
	}
	
	public function indexAction()
	{
		$app_id = isset($_REQUEST['app_id']) ? trim($_REQUEST['app_id']) : '69948denMVMoBujYGLSFGUHbkvP7E3';
        $mediaqihoomodel = new Media_QihooModel($this->_basemodel,$this->_qihooparam);
        $mediaqihoo = $mediaqihoomodel->getApp($app_id);
        if($mediaqihoo)
        {
        	if($mediaqihoo['dic_media_status']==1)
        	{
        		$client_id = ($mediaqihoo['APPID']&&$mediaqihoo['APPKEY'])?$mediaqihoo['APPKEY']:$mediaqihoo['def_APPKEY'];
        		
        		$redirect_uri =  $this->_qihooparam['redirect_uri'];
        		
        		if($mediaqihoo['APPID']&&$mediaqihoo['APPKEY']){
        			$concat = '&';
        			if (strpos($redirect_uri, '?') === false) {
        				$concat = '?';
        			}
        			$redirect_uri.=$concat."app_id={$app_id}";
        			
        			$redirect_uri = $mediaqihoo['receriver_url'] . "{$concat}url=" . urlencode($redirect_uri);
        		}
        		
        		
        		$AppInfo_json = ($mediaqihoo['AppInfo']) ? $mediaqihoo['AppInfo'] : $mediaqihoo['def_AppInfo'];
        		if (!empty($AppInfo_json)) {
        			$AppInfo = json_decode($AppInfo_json, true);
        			$SecretKey = $AppInfo['SecretKey'];
	        		$connection = new Qihoo_QOAuth2($client_id, $SecretKey, '');
	        		$scope = 'basic';
	        		$url = $connection->getAuthorizeURL('code', $redirect_uri, $scope);
	        		header("Location:$url");
        		}
        		else
        		{
        			echo '获取第三方SecretKey失败';
        		}
        	}
        	else
        	{
        		echo '此第三方已经停用';
        	}
        }
        else
        {
        	echo 'error';
        }
        exit;
	}
	
	
	public function callbackAction()
	{
		if (!isset($_REQUEST['code'])) {
			die('{success:false,msg:" code is error"}');
		}
		if (!isset($_REQUEST['app_id'])) {
			die('{success:false,msg:" app_id is error"}');
		}
		$code = $_REQUEST['code'];
		$app_id = isset($_REQUEST['app_id']) ? trim($_REQUEST['app_id']) : '69948denMVMoBujYGLSFGUHbkvP7E3';
		
		$mediaqihoomodel = new Media_QihooModel($this->_basemodel,$this->_qihooparam);
		$mediaqihoo = $mediaqihoomodel->getApp($app_id);
		
		if($mediaqihoo)
		{
			$client_id = ($mediaqihoo['APPID']&&$mediaqihoo['APPKEY'])?$mediaqihoo['APPKEY']:$mediaqihoo['def_APPKEY'];
			$redirect_uri =  $this->_qihooparam['redirect_uri'];
			
			if($mediaqihoo['APPID']&&$mediaqihoo['APPKEY'])
			{
				$concat = '&';
				if (strpos($redirect_uri, '?') === false) {
					$concat = '?';
				}
				$redirect_uri.=$concat."app_id={$app_id}";
			
				$redirect_uri = $mediaqihoo['receriver_url'] . "{$concat}url=" . urlencode($redirect_uri);
			}
			$AppInfo_json = ($mediaqihoo['AppInfo']) ? $mediaqihoo['AppInfo'] : $mediaqihoo['def_AppInfo'];
			if (!empty($AppInfo_json))
			{
				$AppInfo = json_decode($AppInfo_json, true);
				$SecretKey = $AppInfo['SecretKey'];
				$connection = new Qihoo_QOAuth2($client_id, $SecretKey, '');
				$scope = 'basic';
				$access_token_info = $connection->getAccessTokenByCode($code, $redirect_uri);
				
				if($access_token_info)
				{
					$access_token = $access_token_info['access_token'];
					$connection = new Qihoo_QClient($client_id, $SecretKey, $access_token); 
					$userResult = $connection->userMe();

					if ($userResult) {
						$user_data = array(
								'screen_name' => $userResult['name']
								, 'profile_image_url' => $userResult['avatar']
						);
						$media_user_id = $mediaqihoomodel->saveUser(
								$userResult['id']
								, $user_data
						);

						$token_data = array(
								'screenName' => $userResult['name']
								, 'profileImageUrl' => $userResult['avatar']
								, 'access_token' => $access_token_info['access_token']
								, 'id' => $userResult['id']
								, 'scope' => $access_token_info['scope']
								, 'refresh_token' => $access_token_info['refresh_token']
								, 'expires_in' => $access_token_info['expires_in']
						);
						$usertokentime = date('Y-m-d H:i:s',time() + Yaf_Application::app()->getConfig()->application->usertokentime);
						$login_token = $mediaqihoomodel->saveAccessToken($token_data, $media_user_id, $app_id, $usertokentime);
						if ($login_token) {
							$url = $mediaqihoo['token_url'];
							$concat = '&';
							if (strpos($url, '?') === false) {
								$concat = '?';
							}
							$url .= $concat . "token={$login_token}";
							header("Location:$url");
						}
					}
				}
				else
				{
					'获取第三方access_token失败';
				}
			}
			else
			{
				echo '获取第三方SecretKey失败';
			}
		}
		else
		{
			echo 'error';
		}
		exit;
	}
		
}
