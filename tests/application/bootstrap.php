<?php

error_reporting(E_ALL | E_STRICT);
defined('APPLICATION_PATH') || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../../application'));
defined('APPLICATION_ENV') || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'unittest'));
set_include_path(implode(PATH_SEPARATOR, [realpath(APPLICATION_PATH . '/../../library'), get_include_path()]));
require_once 'Zend/Application.php';
require_once 'Zend/Loader/Autoloader.php';
Zend_Loader_Autoloader::getInstance();
require_once 'ControllerTestCase.php';
