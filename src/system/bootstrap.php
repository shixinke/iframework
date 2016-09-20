<?php
defined('SYSTEM_PATH') or define('SYSTEM_PATH', realpath('/'));
defined('ROOT_PATH') or define('ROOT_PATH', realpath('../'));
define('DS', DIRECTORY_SEPARATOR);
defined('APP_PATH') or define('APP_PATH', ROOT_PATH.DS.'apps');
defined('DEBUG') or define('DEBUG', true);
define('SYS_FUNC_PATH', SYSTEM_PATH.DS.'common');
define('SYS_LIB_PATH', SYSTEM_PATH.DS.'libs');
define('SYS_CORE_PATH', SYS_LIB_PATH.DS.'core');
define('SYS_CONF_PATH', SYSTEM_PATH.DS.'config');
define('CONTROLLER_PATH', APP_PATH.DS.'controllers');
define('CONFIG_PATH', APP_PATH.DS.'config');
define('LIB_PATH', APP_PATH.DS.'libs');
define('MODEL_PATH', APP_PATH.DS.'models');
define('VIEWS_PATH', APP_PATH.DS.'views');
define('SERVICE_PATH', APP_PATH.DS.'service');
define('TMP_PATH', ROOT_PATH.DS.'tmp');
define('LOG_PATH', TMP_PATH.DS.'logs');

require_once SYS_FUNC_PATH.DS.'common.php';
require_once SYS_CORE_PATH.DS.'App.php';

\core\App::run();