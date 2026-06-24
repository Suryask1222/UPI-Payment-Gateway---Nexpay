<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/app.php';

spl_autoload_register(function ($class) {
    $baseDir = __DIR__ . '/../app/';
    
    
    $folders = [
        '',
        'controllers/',
        'models/',
        'services/',
        'middleware/',
        'helpers/'
    ];
    
    foreach ($folders as $folder) {
        $file = $baseDir . $folder . $class . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

$pdo = require_once __DIR__ . '/database.php';

require_once __DIR__ . '/../app/helpers/functions.php';
