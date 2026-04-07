<?php
/**
 * Admin Router - OJS Developer Indonesia
 * Routes all admin panel requests
 */

// Harden session cookie before starting
$isSecure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
session_set_cookie_params([
    'lifetime' => 0,
    'path'     => '/',
    'secure'   => $isSecure,
    'httponly' => true,
    'samesite' => 'Lax',
]);
session_start();

// Define admin base path
define('ADMIN_PATH', __DIR__);
define('ROOT_PATH', dirname(__DIR__));

// Check if config exists
$config_file = ROOT_PATH . '/config.php';
if (!file_exists($config_file)) {
    header('Location: ../install.php');
    exit;
}

// Include core files
require_once ROOT_PATH . '/includes/db.php';
require_once ROOT_PATH . '/includes/functions.php';

// Security headers
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Cache-Control: no-store, no-cache, must-revalidate, private');

// Get current page
$page = isset($_GET['page']) ? sanitize($_GET['page']) : 'dashboard';

// Pages that don't require authentication
$public_pages = ['login'];

// Check authentication for protected pages
if (!in_array($page, $public_pages)) {
    if (!isLoggedIn()) {
        flash('error', 'Silakan login terlebih dahulu.');
        redirect('index.php?page=login');
    }
}

// Route to appropriate page
switch ($page) {
    case 'login':
        require_once ADMIN_PATH . '/login.php';
        break;
    
    case 'logout':
        require_once ADMIN_PATH . '/logout.php';
        break;
    
    case 'dashboard':
    case '':
        require_once ADMIN_PATH . '/dashboard.php';
        break;
    
    case 'pesanan':
        require_once ADMIN_PATH . '/pesanan.php';
        break;
    
    case 'pesanan-form':
        require_once ADMIN_PATH . '/pesanan-form.php';
        break;
    
    case 'pesanan-detail':
        require_once ADMIN_PATH . '/pesanan-detail.php';
        break;
    
    case 'portofolio':
        require_once ADMIN_PATH . '/portofolio.php';
        break;
    
    case 'portofolio-form':
        require_once ADMIN_PATH . '/portofolio-form.php';
        break;
    
    case 'blog':
        require_once ADMIN_PATH . '/blog.php';
        break;
    
    case 'blog-form':
        require_once ADMIN_PATH . '/blog-form.php';
        break;
    
    case 'konsultasi':
        require_once ADMIN_PATH . '/konsultasi.php';
        break;
    
    case 'konsultasi-detail':
        require_once ADMIN_PATH . '/konsultasi-detail.php';
        break;
    
    case 'pengaturan':
        require_once ADMIN_PATH . '/pengaturan.php';
        break;
    
    case 'export':
        require_once ADMIN_PATH . '/export.php';
        break;
    
    default:
        // 404 for unknown pages
        require_once ADMIN_PATH . '/includes/header.php';
        require_once ADMIN_PATH . '/includes/sidebar.php';
        ?>
        <div class="admin-content">
            <div class="container-fluid py-4">
                <div class="text-center py-5">
                    <i class="fas fa-exclamation-triangle fa-4x text-warning mb-3"></i>
                    <h2>Halaman Tidak Ditemukan</h2>
                    <p class="text-muted">Halaman yang Anda cari tidak tersedia.</p>
                    <a href="index.php" class="btn btn-primary">
                        <i class="fas fa-home me-2"></i>Kembali ke Dashboard
                    </a>
                </div>
            </div>
        </div>
        <?php
        require_once ADMIN_PATH . '/includes/footer.php';
        break;
}
