<?php

if (!defined('SITE_NAME')) {
    define('SITE_NAME', 'Nex Pay');
}

if (!defined('BASE_URL')) {
    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
    $basePath = '';
    if ($scriptName) {
        $basePath = str_replace('\\', '/', dirname($scriptName));
        if ($basePath === '/' || $basePath === '.') {
            $basePath = '';
        } else {
            $basePath = rtrim($basePath, '/');
        }
    }
    define('BASE_URL', $basePath);
}

ini_set('display_errors', 0);
error_reporting(E_ALL);

date_default_timezone_set('Asia/Kolkata');
