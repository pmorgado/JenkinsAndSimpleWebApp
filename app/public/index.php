<?php
ini_set('display_errors', 1);
define('APP_PATH', dirname(__FILE__) . '/../');
require_once APP_PATH . 'Bootstrap.php';
$app = new Bootstrap();
$app->init();


