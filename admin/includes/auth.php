<?php
/**
 * Admin Authentication Check
 * Include this file to protect admin pages
 */

if (!function_exists('isLoggedIn')) {
    session_start();
    define('ROOT_PATH', dirname(dirname(__DIR__)));
    require_once ROOT_PATH . '/includes/functions.php';
}

if (!isLoggedIn()) {
    flash('error', 'Silakan login terlebih dahulu untuk mengakses halaman ini.');
    redirect('index.php?page=login');
}
