<?php

/**
 * @name BaseDbModel
 * @author root
 */
class UserModel {

    private $_db;
    private $_basemodel;

    public function __construct($basedb) {
        $this->_db = $basedb->_db;
        $this->_basemodel = $basedb;
    }

    public function getApp($app_id){
        return  $this->_basemodel->get(array('*'),'app',array("app_id='{$app_id}'"));
    }
    public function getMedia_user($app_id,$token){
        $now=  date('Y-m-d H-i-s');
        $media_user = $this->_basemodel->get(array('media_user.*'),'`login_token`', 
                array("login_token.app_id='{$app_id}'","login_token.token='{$token}'","login_token.status=1","login_token.e_time>='{$now}'"),
                array(array('table' => '`media_user`', 'on' => '`login_token`.`media_user_id`=`media_user`.`mediaUserID`', 'type' => 'left'))
                        );
        if($media_user){
            $this->_basemodel->update('login_token',array('`u_time`'=>$now,'`status`'=>0),array("login_token.app_id='{$app_id}'","login_token.token='{$token}'"));
        }
        return $media_user;
    }
    
}
