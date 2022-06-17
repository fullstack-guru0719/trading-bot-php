<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 'On');
ini_set('error_log', __DIR__ . '/../../system.log');
ini_set('memory_limit', '32M');

define('CRYPTOBOT', 1);

$cryptobot_language = 'en';
if (isset($_GET['l']) && in_array($_GET['l'], array('en', 'tr'))) {
    $cryptobot_language = $_GET['l'];
}
define('CRYPTOBOT_LANGUAGE', $cryptobot_language);
