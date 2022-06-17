<?php
require_once(dirname(__FILE__) . '/../../vendor/autoload.php');

spl_autoload_register(function($className) {
    $file = dirname(__FILE__) . '/../php/class/' . $className . '.php';
    if (file_exists($file)) {
        include($file);
    }
});