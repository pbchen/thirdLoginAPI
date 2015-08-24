<?php
/**
 * Created by JetBrains PhpStorm.
 * User: zhaozhengguang
 * Date: 13-10-28
 * Time: 下午3:09
 * To change this template use File | Settings | File Templates.
 */
class Media_renrenModel
{

    private $_renrenparam;
    private $_db;
    private $_basemodel;

    public function __construct($basedb, $renrenparam = array())
    {
        $this->_renrenparam = $renrenparam;
        $this->_db = $basedb->_db;
        $this->_basemodel = $basedb;
    }

    public function getApp($app_id)
    {
        $clos = array(
            '`app`.`id` AS `id`'
        , '`app`.`app_id` AS `app_id`'
        , '`app`.`app_key` AS `app_key`'
        , '`app`.`token_url` AS `token_url`'
        , '`app`.`receriver_url` AS `receriver_url`'
        , '`app`.`bind_url` AS `bind_url`'
        , '`app_media`.`media_id` AS `media_id`'
        , '`app_media`.`APPID` AS `APPID`'
        , '`app_media`.`APPKEY` AS `APPKEY`'
        , '`app_media`.`AppInfo` AS `AppInfo`'
        , '`dic_media`.`def_APPID` AS `def_APPID`'
        , '`dic_media`.`def_APPKEY` AS `def_APPKEY`'
        , '`dic_media`.`status` AS `dic_media_status`'
        , '`dic_media`.`def_AppInfo` AS `def_AppInfo`'
        );
        $table = '`app`';
        $where = array("`app`.`app_id`='{$app_id}'", "`app_media`.`media_id`=" . $this->_renrenparam['media_id']);
        $join = array(array('table' => '`app_media`', 'on' => '`app_media`.`app_id`=`app`.`id`', 'type' => 'left')
        , array('table' => '`dic_media`', 'on' => '`dic_media`.`id` = `app_media`.`media_id`', 'type' => 'left'));

        $mediarenren = $this->_basemodel->get($clos, $table, $where, $join);
        return $mediarenren;
    }

    public function saveUser($uid, $renrenUser)
    {
        $result = FALSE;
        $media_renren_user_id = $this->_basemodel->get(array('id'), 'media_renren_user', array("uid='{$uid}'"));
        if (!$media_renren_user_id) {
            $media_renren_user_id = $this->_basemodel->insert('media_renren_user', array('uid' => $uid), TRUE);
        } else {
            $media_renren_user_id = $media_renren_user_id['id'];
        }
        if ($media_renren_user_id) {
            $data = array(
                'mediaUserID' => 10000000000 * (int)$this->_renrenparam['media_id'] + $media_renren_user_id,
                'mediaID' => (int)$this->_renrenparam['media_id'],
                'screenName' => $renrenUser['screen_name'],
                'profileImageUrl' => $renrenUser['profile_image_url']
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
        $now = date('Y-m-d H:i:s');
        $e_time = $usertokentime;
        $access_token = md5($now . uniqid() . $data['access_token'] . $data['id']);
        $uid = $data['id'];
        $data = array(
            'c_time' => $now,
            'token_type' => $data['type'],
            'access_token' => $access_token,
            'refresh_token' => $data['refresh_token'],
            'uid' => $uid
        );
//        print_r($data);
        //先保存renren的token，后期可能使用它来进行后续处理
        $this->_basemodel->delete('login_media_renren_token', array("uid='{$uid}'"));
        if ($this->_basemodel->insert('login_media_renren_token', $data)) {
            $login_token_data = array(
                'c_time' => $now,
                'e_time' => $e_time,
                'status' => 1,
                'app_id' => $app_id,
                'token' => $access_token,
                'media_id' => (int)$this->_renrenparam['media_id'],
                'media_user_id' => $media_user_id,
            );
            if ($this->_basemodel->insert('login_token', $login_token_data)) {
                $result = $access_token;
            }
        }
        return $result;

    }

}
