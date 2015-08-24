<?php

define('APPLICATION_PATH', dirname(__FILE__));
@ini_set('date.timezone', 'Asia/Shanghai');
$application = new Yaf_Application( APPLICATION_PATH . "/conf/application.ini");

$application->bootstrap()->run();
?>
