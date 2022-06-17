<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once("site/config/settings.php");
require_once("site/config/auto_loaders.php");
require_once("site/config/db.php");

//defaults
$response                      = array();
$response['result']            = ''; //success or error
$response['error']             = '';
$response['error_description'] = '';
$response['values']            = array();
$has_error                     = false;

require_once('site/php/model/api.php');

show_response:
$response['result'] = ($has_error ? 'error' : 'success');

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

header("Expires: Tue, 03 Jul 2001 06:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

echo json_encode($response);
