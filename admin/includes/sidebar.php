<?php
/**
 * Admin Sidebar Navigation
 */

$current_page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
$site_name = function_exists('getSetting') ? getSetting('site_name', 'OJS Developer') : 'OJS Developer';

// Get new konsultasi count for badge
$new_count = 0;
if (function_exists('fetch')) {
    $cnt = fetch("SELECT COUNT(*) as cnt FROM consultations WHERE status = 'new'");
    $new_count = $cnt ? (int)$cnt['cnt'] : 0;
}

// Get pending orders count for badge
$pending_orders = 0;
if (function_exists('fetch')) {
    try {
        $oc = fetch("SELECT COUNT(*) as cnt FROM orders WHERE status IN ('pending','in_progress')");
        $pending_orders = $oc ? (int)$oc['cnt'] : 0;
    } catch (Exception $e) { /* table may not exist yet */ }
}

$menu_items = [
    ['page' => 'dashboard',    'icon' => 'fa-chart-line',        'label' => 'Dashboard'],
    ['page' => 'pesanan',      'icon' => 'fa-clipboard-list',    'label' => 'Pesanan',     'badge' => $pending_orders, 'match' => ['pesanan','pesanan-form','pesanan-detail']],
    ['page' => 'portofolio',   'icon' => 'fa-briefcase',         'label' => 'Portofolio'],
    ['page' => 'blog',         'icon' => 'fa-newspaper',         'label' => 'Blog'],
    ['page' => 'konsultasi',   'icon' => 'fa-comments',          'label' => 'Konsultasi', 'badge' => $new_count],
    ['page' => 'pengaturan',   'icon' => 'fa-cog',               'label' => 'Pengaturan'],
    ['page' => 'export',       'icon' => 'fa-download',          'label' => 'Export Data'],
];

// Get admin initials for avatar
$admin_name = isset($_SESSION['admin_name']) ? $_SESSION['admin_name'] : 'Admin';
$initials = '';
$parts = explode(' ', $admin_name);
foreach (array_slice($parts, 0, 2) as $p) {
    $initials .= strtoupper(substr($p, 0, 1));
}
?>
<!-- Admin Sidebar -->
<aside class="admin-sidebar" id="adminSidebar">
    <!-- Brand -->
    <a href="index.php" class="sidebar-brand">
        <div class="sidebar-brand-icon">
            <i class="fas fa-journal-whills"></i>
        </div>
        <div class="sidebar-brand-text">
            <span class="sidebar-brand-name"><?= htmlspecialchars($site_name) ?></span>
            <span class="sidebar-brand-sub">Admin Panel</span>
        </div>
    </a>

    <!-- Navigation -->
    <nav class="sidebar-nav">
        <div class="sidebar-section-title">Menu Utama</div>

        <?php foreach ($menu_items as $item): ?>
        <div class="sidebar-nav-item">
            <a href="index.php?page=<?= $item['page'] ?>"
               class="sidebar-nav-link <?php
                    $isActive = ($current_page === $item['page']);
                    if (!$isActive && !empty($item['match'])) {
                        $isActive = in_array($current_page, $item['match']);
                    }
                    echo $isActive ? 'active' : '';
               ?>">
                <span class="sidebar-nav-icon">
                    <i class="fas <?= $item['icon'] ?>"></i>
                </span>
                <span class="sidebar-nav-label"><?= $item['label'] ?></span>
                <?php if (!empty($item['badge']) && $item['badge'] > 0): ?>
                    <span class="sidebar-badge"><?= $item['badge'] ?></span>
                <?php endif; ?>
            </a>
        </div>
        <?php endforeach; ?>

        <div class="sidebar-divider"></div>
        <div class="sidebar-section-title">Lainnya</div>

        <!-- Back to Website -->
        <div class="sidebar-nav-item">
            <a href="../index.php" target="_blank" class="sidebar-nav-link">
                <span class="sidebar-nav-icon">
                    <i class="fas fa-external-link-alt"></i>
                </span>
                <span class="sidebar-nav-label">Lihat Website</span>
            </a>
        </div>

        <!-- Logout -->
        <div class="sidebar-nav-item">
            <a href="index.php?page=logout"
               class="sidebar-nav-link text-danger-soft"
               onclick="return confirm('Yakin ingin keluar dari panel admin?')">
                <span class="sidebar-nav-icon">
                    <i class="fas fa-sign-out-alt"></i>
                </span>
                <span class="sidebar-nav-label">Keluar</span>
            </a>
        </div>
    </nav>

    <!-- Sidebar Footer / User Info -->
    <div class="sidebar-footer">
        <div class="sidebar-user">
            <div class="sidebar-avatar"><?= htmlspecialchars($initials ?: 'A') ?></div>
            <div class="sidebar-user-info">
                <span class="sidebar-user-name"><?= htmlspecialchars($admin_name) ?></span>
                <span class="sidebar-user-role">Administrator</span>
            </div>
        </div>
    </div>
</aside>

<!-- Admin Main Content Wrapper -->
<div class="admin-main" id="adminMain">
    <!-- Top Navigation Bar -->
    <header class="admin-topbar">
        <button class="topbar-toggle" id="sidebarToggle" onclick="toggleSidebar()">
            <i class="fas fa-bars"></i>
        </button>

        <div class="topbar-breadcrumb">
            <h1>
                <?php
                $titles = [
                    'dashboard'          => 'Dashboard',
                    'pesanan'            => 'Pesanan',
                    'pesanan-form'       => isset($_GET['id']) ? 'Edit Pesanan' : 'Buat Pesanan Baru',
                    'pesanan-detail'     => 'Detail Pesanan',
                    'portofolio'         => 'Portofolio',
                    'portofolio-form'    => isset($_GET['id']) ? 'Edit Portofolio' : 'Tambah Portofolio',
                    'blog'               => 'Blog',
                    'blog-form'          => isset($_GET['id']) ? 'Edit Artikel' : 'Tambah Artikel',
                    'konsultasi'         => 'Konsultasi',
                    'konsultasi-detail'  => 'Detail Konsultasi',
                    'pengaturan'         => 'Pengaturan',
                    'export'             => 'Export Data',
                ];
                echo htmlspecialchars($titles[$current_page] ?? 'Admin Panel');
                ?>
            </h1>
        </div>

        <div class="topbar-actions">
            <?php if ($new_count > 0): ?>
            <a href="index.php?page=konsultasi" class="topbar-icon-btn" title="Konsultasi Baru">
                <i class="fas fa-bell"></i>
                <span class="topbar-notification-dot"></span>
            </a>
            <?php endif; ?>

            <a href="../index.php" target="_blank" class="topbar-icon-btn" title="Lihat Website">
                <i class="fas fa-external-link-alt"></i>
            </a>

            <!-- User Dropdown -->
            <div class="dropdown">
                <div class="topbar-user dropdown-toggle" data-bs-toggle="dropdown" style="cursor:pointer;">
                    <div class="topbar-avatar"><?= htmlspecialchars($initials ?: 'A') ?></div>
                    <span class="topbar-user-name"><?= htmlspecialchars($admin_name) ?></span>
                </div>
                <ul class="dropdown-menu dropdown-menu-end shadow border-0" style="border-radius:10px; min-width:180px; margin-top:6px;">
                    <li><h6 class="dropdown-header">Akun Admin</h6></li>
                    <li>
                        <a class="dropdown-item" href="index.php?page=pengaturan&tab=akun">
                            <i class="fas fa-key me-2 text-secondary"></i>Ubah Password
                        </a>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <a class="dropdown-item text-danger" href="index.php?page=logout"
                           onclick="return confirm('Yakin ingin keluar?')">
                            <i class="fas fa-sign-out-alt me-2"></i>Keluar
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </header>
