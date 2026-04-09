<?php
/**
 * OJS Developer Website - Front Controller
 * Routes all requests to the appropriate page file.
 */

// If not installed, redirect to installer
if (!file_exists(__DIR__ . '/config.php')) {
    header('Location: install.php');
    exit;
}

// Load configuration and core includes
// require_once __DIR__ . '/config.php'; // This will be created by install.php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

// Harden session cookie before starting
if (session_status() === PHP_SESSION_NONE) {
    $isSecure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'secure'   => $isSecure,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

// Security headers
header_remove('X-Powered-By');
ini_set('display_errors', 0);
error_reporting(0);
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');

// ──────────────────────────────────────────
// PARSE REQUEST URI → determine page
// ──────────────────────────────────────────
$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$scriptName = dirname($_SERVER['SCRIPT_NAME']);
$basePath   = rtrim($scriptName, '/');

// Strip base path and query string
$path = $requestUri;
if (!empty($basePath) && strpos($path, $basePath) === 0) {
    $path = substr($path, strlen($basePath));
}
$path = strtok($path, '?'); // Remove query string
$path = trim($path, '/');

// Query-string based routing fallback (for hosts without mod_rewrite)
if (isset($_GET['page'])) {
    $page = $_GET['page'];
} else {
    // Map clean URL segments to page names
    $segments = explode('/', $path);
    $firstSeg = $segments[0] ?? '';
    $secondSeg = $segments[1] ?? '';

    switch ($firstSeg) {
        case '':
        case 'home':
            $page = 'home';
            break;
        case 'layanan':
            $page = 'layanan';
            break;
        case 'portofolio':
            if (!empty($secondSeg)) {
                $_GET['slug'] = $secondSeg;
                $page = 'portofolio-detail';
            } else {
                $page = 'portofolio';
            }
            break;
        case 'blog':
            if (!empty($secondSeg)) {
                $_GET['slug'] = $secondSeg;
                $page = 'blog-detail';
            } else {
                $page = 'blog';
            }
            break;
        case 'harga':
            $page = 'harga';
            break;
        case 'tentang':
            $page = 'tentang';
            break;
        case 'konsultasi':
            $page = 'konsultasi';
            break;
        case 'tracking':
            $page = 'tracking';
            break;
        default:
            $page = '404';
            break;
    }
}

// Sanitize page name — only allow alphanumeric, hyphen, underscore
$page = preg_replace('/[^a-zA-Z0-9\-_]/', '', $page);

// Build page file path
$pageFile = __DIR__ . '/pages/' . $page . '.php';

// Handle AJAX submissions before output starts (konsultasi + tracking)
if (in_array($page, ['konsultasi', 'tracking']) && $_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
    require_once $pageFile;
    exit;
}

// ──────────────────────────────────────────
// RENDER
// ──────────────────────────────────────────

// Determine page title (individual pages can override $pageTitle)
$pageTitle = getSetting('site_name', 'OJS Developer Indonesia');
$currentPage = $page ?: 'home';

// Load the page to allow it to set $pageTitle and $metaDesc before header
ob_start();
if (file_exists($pageFile)) {
    require $pageFile;
} else {
    http_response_code(404);
    echo '<div class="container py-5 text-center"><h1>404</h1><p>Halaman tidak ditemukan.</p><a href="' . (defined('SITE_URL') ? SITE_URL : '/') . '" class="btn btn-primary">Kembali ke Beranda</a></div>';
}
$pageContent = ob_get_clean();

// Now output header, page content, footer
require __DIR__ . '/includes/header.php';
echo $pageContent;
require __DIR__ . '/includes/footer.php';
