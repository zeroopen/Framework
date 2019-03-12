<?php

define('DEBUG', true);
define('SELF_PATH', dirname(__FILE__) . '/');
define('ROOT_PATH', dirname(SELF_PATH) . '/');

require ROOT_PATH . 'framework/Framework.php';
Framework::start();
if (INSTALL) {
	Framework::openMobile();
	Framework::module(array('Home', 'User', 'Article'), 'Article');
} else {
	Framework::module(array('Install'));
}
$object = Framework::getObject('Index');
Framework::run($object) or exit('系统错误');