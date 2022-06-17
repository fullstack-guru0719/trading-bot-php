<?php
require_once("site/config/settings.php");
require_once("site/config/auto_loaders.php");
require_once("site/config/db.php");

$site_info          = array();
$site_info['title'] = 'CryptoBot For Binance';

$result_array                 = array();
$result_array['page_success'] = true;
$result_array['page_errors']  = array();

require_once('site/php/model/settings.php');

$data                 = array();
$data['current_page'] = 'settings';
$data['href_format']  = 'href="%s"';
$data['src_format']   = 'src="%s"';
$data['site_info']    = $site_info;
$data['result_array'] = $result_array;
$data['page_vars']    = isset($page_vars) ? $page_vars : array();

header("Expires: Tue, 03 Jul 2001 06:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

require_once 'ui/settings.php';
