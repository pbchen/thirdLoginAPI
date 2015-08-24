<?php
/**
 * @name IndexController
 * @author root
 * @desc 默认控制器
 * @see http://www.php.net/manual/en/class.yaf-controller-abstract.php
 */
class LoginController extends Yaf_Controller_Abstract {

	public function indexAction() {
		$token = $this->getRequest()->getQuery("token", "NULL");
		$this->getView()->assign("token", $token);
        return TRUE;
	}
}
