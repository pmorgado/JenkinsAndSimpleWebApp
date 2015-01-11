<?php
ini_set('display_errors', 1);
if (!isset($_SERVER['HTTP_HOST'])) {
    header($_SERVER['SERVER_PROTOCOL'].' 400 Bad Request');
    exit;
}
define('DOMAIN', 'http://' . $_SERVER['HTTP_HOST']);
define('APP_PATH', dirname(__FILE__) . '/../');
require_once APP_PATH . 'Bootstrap.php';
$app = new Bootstrap();
$app->init();


