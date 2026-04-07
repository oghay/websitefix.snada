<?php
/**
 * Database Connection - OJS Developer Indonesia
 * This file is auto-generated or configured during installation.
 * Uses PDO with MySQLi for prepared statements.
 */

// Database configuration is loaded from config.php
$config_file = dirname(__DIR__) . '/config.php';

if (file_exists($config_file)) {
    require_once $config_file;
} else {
    // config.php does not exist - redirect to installer
    header('Location: /install.php');
    exit;
}

// PDO connection is established in functions.php via db() singleton
// This file just ensures config constants are defined if not already
if (!defined('DB_HOST')) {
    define('DB_HOST', 'localhost');
    define('DB_NAME', 'ojs_developer');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('DB_CHARSET', 'utf8mb4');
}
