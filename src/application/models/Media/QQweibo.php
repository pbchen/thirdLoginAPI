<?php

/**
 * @name BaseDbModel
 * @author root
 */
class Media_QQweiboModel {

    private $_weiboparam;
    private $_db;
    private $_basemodel;

    public function __construct($basedb,$qqparam=array()) {
        $this->_weiboparam=array_merge(array('media_id'=>4),$qqparam);
        $this->_db = $basedb->_db;
        $this->_basemodel = $basedb;
    }

    public function getApp($app_id){
        $clos = array(
            '`app`.`id` AS `id`,`app`.`app_id` AS `app_id`,`app`.`app_key` AS `app_key`',
            '`app`.`token_url` AS `token_url`,`app`.`receriver_url` AS `receriver_url`,`app`.`bind_url` AS `bind_url`', 
            '`app_media`.`media_id` AS `media_id`,`app_media`.`APPID` AS `APPID`,`app_media`.`APPKEY` AS `APPKEY`',
            '`dic_media`.`def_APPID` AS `def_APPID`,`dic_media`.`def_APPKEY` AS `def_APPKEY`,`dic_media`.`status` AS `dic_media_status`');
        $table = '`app`';
        $where = array("`app`.`app_id`='{$app_id}'", "`app_media`.`media_id`=" . $this->_weiboparam['media_id']);
        $join = array(array('table' => '`app_media`', 'on' => '`app_media`.`app_id`=`app`.`id`', 'type' => 'left')
            , array('table' => '`dic_media`', 'on' => '`dic_media`.`id` = `app_media`.`media_id`', 'type' => 'left'));
        
        $meidaqq = $this->_basemodel->get($clos, $table, $where, $join);
        return $meidaqq;
    }
    
    public function saveUser($openid,$qqUser,$APPID){
        $result=FALSE;
        $media_qq_user_id = $this->_basemodel->get(array('id'),'media_qqweibo_user',array("openid='{$openid}'","APPID='{$APPID}'"));
        if(!$media_qq_user_id){
            $media_qq_user_id = $this->_basemodel->insert('media_qqweibo_user',array('openid'=>$openid,'APPID'=>$APPID),TRUE);
        }else{
            $media_qq_user_id = $media_qq_user_id['id'];
        }
        if($media_qq_user_id){
            $data=array(
                'mediaUserID'=> 10000000000 * (int)$this->_weiboparam['media_id'] + $media_qq_user_id,
                'mediaID'=>(int)$this->_weiboparam['media_id'],
                'screenName'=>$qqUser['nick'],
                'profileImageUrl'=>$qqUser['head']
            );
//            print_r($data);
            if($this->_basemodel->replace('media_user',$data)){
                $result=$data['mediaUserID'];
            }
        }
        return $result;
    }
    
    public function saveAccessToken($app_id,$media_qq_user_id,$usertokentime){
        
        
        $result=FALSE;
        $nowT=time();
        $now=  date('Y-m-d H-i-s',$nowT);
        $e_time = $usertokentime;
        $login_token = md5($now.  uniqid());
        $data= array(
            'c_time'=>$now,
            'login_token'=>  $login_token,
            'qqweibo_access_token'=>$_SESSION['t_access_token'],
            'qqweibo_refresh_token'=>$_SESSION['t_refresh_token'],
            'qqweibo_expire_in'=>$_SESSION['t_expire_in'],
            'qqweibo_openid'=>$_SESSION['t_openid']
        );
//        print_r($data);
        //先保存QQ的token，后期可能使用它来进行后续处理
        $this->_basemodel->delete('login_media_qqweibo_token',array("qqweibo_openid='{$_SESSION['t_openid']}'"));
        if($this->_basemodel->insert('login_media_qqweibo_token',$data)){
            $login_token_data = array(
                'c_time'=>$now,
                'e_time'=>$e_time,
                'status'=>1,
                'app_id'=>  $app_id,
                'token'=>$login_token,
                'media_id'=>(int)$this->_weiboparam['media_id'],
                'media_user_id'=>$media_qq_user_id,
            );
            
            if($this->_basemodel->insert('login_token',$login_token_data)){
                $result=$login_token;
            }
        }
        return $result;
        
    }
    
}
