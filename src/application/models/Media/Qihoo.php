<?php
/**
 * @name BaseDbModel
 * @author root
 */
class Media_QihooModel {

	private $_qihooparam;
	private $_db;
	private $_basemodel;
	
	public function __construct($basedb,$qihooparam=array()) {
		$this->_qihooparam=array_merge(array('media_id'=>23),$qihooparam);
		$this->_db = $basedb->_db;
		$this->_basemodel = $basedb;
	}
	
	public function getApp($app_id){
		$clos = array(
				'`app`.`id` AS `id`,`app`.`app_id` AS `app_id`,`app`.`app_key` AS `app_key`',
				'`app`.`token_url` AS `token_url`,`app`.`receriver_url` AS `receriver_url`,`app`.`bind_url` AS `bind_url`',
				'`app_media`.`media_id` AS `media_id`,`app_media`.`APPID` AS `APPID`,`app_media`.`APPKEY` AS `APPKEY`',
				'`dic_media`.`def_APPID` AS `def_APPID`,`dic_media`.`def_APPKEY` AS `def_APPKEY`,`dic_media`.`status` AS `dic_media_status`', 
				'`dic_media`.`def_AppInfo` AS `def_AppInfo`', '`app_media`.`AppInfo` AS `AppInfo`');
		$table = '`app`';
		$where = array("`app`.`app_id`='{$app_id}'", "`app_media`.`media_id`=" . $this->_qihooparam['media_id']);
		$join = array(array('table' => '`app_media`', 'on' => '`app_media`.`app_id`=`app`.`id`', 'type' => 'left')
				, array('table' => '`dic_media`', 'on' => '`dic_media`.`id` = `app_media`.`media_id`', 'type' => 'left'));
	
		$meidaqq = $this->_basemodel->get($clos, $table, $where, $join);
		return $meidaqq;
	}
	
	public function saveUser($uid, $qihooUser)
	{
		$result = FALSE;
		$media_qihoo_user_id = $this->_basemodel->get(array('id'), 'media_qihoo_user', array("uid='{$uid}'"));
		if (!$media_qihoo_user_id) {
			$media_qihoo_user_id = $this->_basemodel->insert('media_qihoo_user', array('uid' => $uid), TRUE);
		} else {
			$media_qihoo_user_id = $media_qihoo_user_id['id'];
		}
		if ($media_qihoo_user_id) {
			$data = array(
					'mediaUserID' => 10000000000 * (int)$this->_qihooparam['media_id'] + $media_qihoo_user_id,
					'mediaID' => (int)$this->_qihooparam['media_id'],
					'screenName' => $qihooUser['screen_name'],
					'profileImageUrl' => $qihooUser['profile_image_url']
			);
			//            print_r($data);
			if ($this->_basemodel->replace('media_user', $data)) {
				$result = $data['mediaUserID'];
			}
		}
		return $result;
	}
	
	public function saveAccessToken( $data, $media_user_id, $app_id, $usertokentime )
	{
		$result = FALSE;
		$nowT = time();
		$now = date('Y-m-d H:i:s', $nowT);
		$e_time = $usertokentime;
		$login_token = md5($now . uniqid() . $data['access_token'] . $data['id']);
		$uid = $data['id'];
		$data = array(
				'c_time' => $now,
				'login_token' => $login_token,
				'qihoo_access_token'=> $data['access_token'],
				'qihoo_uid' => $uid,
				'qihoo_expires_in'=> $data['expires_in'],
				'qihoo_refresh_token' => $data['refresh_token'],
				'scope'=> $data['scope']
		);
		//        print_r($data);
		//先保存renren的token，后期可能使用它来进行后续处理
		$this->_basemodel->delete('login_media_qihoo_token', array("qihoo_uid='{$uid}'"));
		if ($this->_basemodel->insert('login_media_qihoo_token', $data)) {
			$login_token_data = array(
					'c_time' => $now,
					'e_time' => $e_time,
					'status' => 1,
					'app_id' => $app_id,
					'token' => $login_token,
					'media_id' => (int)$this->_qihooparam['media_id'],
					'media_user_id' => $media_user_id,
			);
			if ($this->_basemodel->insert('login_token', $login_token_data)) {
				$result = $login_token;
			}
		}
		return $result;
	
	}
}