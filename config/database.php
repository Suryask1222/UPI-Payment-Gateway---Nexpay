<?php
//  database connection
if (!isset($pdo)) {
    //local db connection use remote if needed 
    if (!defined('DB_HOST'))
        define('DB_HOST', 'localhost');
    if (!defined('DB_NAME'))
        define('DB_NAME', 'nexpay');
    if (!defined('DB_USER'))
        define('DB_USER', 'surya');
    if (!defined('DB_PASS'))
        define('DB_PASS', 'Suryask@12');

    try {
        $pdo = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
            DB_USER,
            DB_PASS,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]
        );
    } catch (PDOException $e) {
        error_log("Database connection error: " . $e->getMessage());
        die("Database connection could not be established. Please check your credentials.");
    }
}

return $pdo;

