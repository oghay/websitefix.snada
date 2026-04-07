<?php
/**
 * Admin Logout
 */

// Clear all session data
$_SESSION = [];

// Destroy the session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// Destroy session
session_destroy();

// Redirect to login with message
// Start new session for flash
session_start();
flash('success', 'Anda telah berhasil keluar dari panel admin.');
redirect('index.php?page=login');
