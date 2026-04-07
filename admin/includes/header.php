<?php
/**
 * Admin Header - OJS Developer Indonesia
 * Includes CDN links and comprehensive admin CSS
 */

// Get site settings
$site_name = function_exists('getSetting') ? getSetting('site_name', 'OJS Developer Indonesia') : 'OJS Developer Indonesia';
$admin_name = isset($_SESSION['admin_name']) ? $_SESSION['admin_name'] : 'Admin';
$current_page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';

// Get new consultation count for badge
$new_konsultasi_count = 0;
if (function_exists('fetch')) {
    $count_result = fetch("SELECT COUNT(*) as cnt FROM consultations WHERE status = 'new'");
    $new_konsultasi_count = $count_result ? (int)$count_result['cnt'] : 0;
}

$page_title = 'Admin Panel';
switch ($current_page) {
    case 'dashboard': $page_title = 'Dashboard'; break;
    case 'portofolio': $page_title = 'Kelola Portofolio'; break;
    case 'portofolio-form': $page_title = isset($_GET['id']) ? 'Edit Portofolio' : 'Tambah Portofolio'; break;
    case 'blog': $page_title = 'Kelola Blog'; break;
    case 'blog-form': $page_title = isset($_GET['id']) ? 'Edit Artikel' : 'Tambah Artikel'; break;
    case 'konsultasi': $page_title = 'Manajemen Konsultasi'; break;
    case 'konsultasi-detail': $page_title = 'Detail Konsultasi'; break;
    case 'pengaturan': $page_title = 'Pengaturan Situs'; break;
    case 'export': $page_title = 'Export Data'; break;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?> - Admin <?= htmlspecialchars($site_name) ?></title>

    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Bootstrap 5.3.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome 6.5 -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css" rel="stylesheet">

    <style>
        /* ============================================================
           ADMIN PANEL - OJS Developer Indonesia
           Comprehensive Admin Stylesheet
        ============================================================ */

        /* --- CSS Custom Properties --- */
        :root {
            --sidebar-width: 260px;
            --sidebar-collapsed-width: 70px;
            --sidebar-bg: #1e293b;
            --sidebar-hover: #334155;
            --sidebar-active: #0d9488;
            --sidebar-active-bg: rgba(13, 148, 136, 0.15);
            --sidebar-text: #cbd5e1;
            --sidebar-heading: #64748b;
            --topbar-height: 64px;
            --topbar-bg: #ffffff;
            --content-bg: #f1f5f9;
            --card-bg: #ffffff;
            --card-shadow: 0 1px 3px rgba(0,0,0,0.08), 0 4px 16px rgba(0,0,0,0.04);
            --card-shadow-hover: 0 4px 12px rgba(0,0,0,0.12), 0 8px 32px rgba(0,0,0,0.08);
            --border-color: #e2e8f0;
            --text-primary: #1e293b;
            --text-secondary: #64748b;
            --text-muted: #94a3b8;
            --primary: #1a365d;
            --primary-hover: #162d4f;
            --secondary: #0d9488;
            --secondary-hover: #0b7a6e;
            --accent: #d97706;
            --success: #16a34a;
            --warning: #d97706;
            --danger: #dc2626;
            --info: #0891b2;
            --radius-sm: 6px;
            --radius-md: 10px;
            --radius-lg: 14px;
            --radius-xl: 20px;
            --transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            --font-main: 'Plus Jakarta Sans', 'Helvetica Neue', Arial, sans-serif;
        }

        /* --- Base Reset --- */
        *, *::before, *::after {
            box-sizing: border-box;
        }

        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: var(--font-main);
            font-size: 14px;
            line-height: 1.6;
            color: var(--text-primary);
            background-color: var(--content-bg);
            overflow-x: hidden;
        }

        /* --- Admin Layout Wrapper --- */
        .admin-wrapper {
            display: flex;
            min-height: 100vh;
            position: relative;
        }

        /* ============================================================
           SIDEBAR
        ============================================================ */
        .admin-sidebar {
            width: var(--sidebar-width);
            background: var(--sidebar-bg);
            min-height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1030;
            display: flex;
            flex-direction: column;
            transition: var(--transition);
            overflow: hidden;
        }

        .admin-sidebar.collapsed {
            width: var(--sidebar-collapsed-width);
        }

        /* Sidebar Brand */
        .sidebar-brand {
            padding: 20px 16px;
            display: flex;
            align-items: center;
            gap: 12px;
            border-bottom: 1px solid rgba(255,255,255,0.06);
            min-height: var(--topbar-height);
            text-decoration: none;
            flex-shrink: 0;
        }

        .sidebar-brand-icon {
            width: 38px;
            height: 38px;
            background: linear-gradient(135deg, var(--secondary), #0f766e);
            border-radius: var(--radius-md);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            font-size: 18px;
            color: #fff;
        }

        .sidebar-brand-text {
            flex: 1;
            overflow: hidden;
        }

        .sidebar-brand-name {
            display: block;
            font-weight: 700;
            font-size: 14px;
            color: #f1f5f9;
            white-space: nowrap;
            line-height: 1.2;
        }

        .sidebar-brand-sub {
            display: block;
            font-size: 11px;
            color: var(--sidebar-heading);
            white-space: nowrap;
        }

        /* Sidebar Navigation */
        .sidebar-nav {
            flex: 1;
            padding: 12px 0;
            overflow-y: auto;
            overflow-x: hidden;
        }

        .sidebar-nav::-webkit-scrollbar {
            width: 4px;
        }

        .sidebar-nav::-webkit-scrollbar-track {
            background: transparent;
        }

        .sidebar-nav::-webkit-scrollbar-thumb {
            background: rgba(255,255,255,0.1);
            border-radius: 2px;
        }

        .sidebar-section-title {
            padding: 8px 20px 4px;
            font-size: 10px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: var(--sidebar-heading);
            white-space: nowrap;
            overflow: hidden;
        }

        .sidebar-nav-item {
            margin: 2px 10px;
        }

        .sidebar-nav-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 12px;
            border-radius: var(--radius-md);
            color: var(--sidebar-text);
            text-decoration: none;
            font-weight: 500;
            font-size: 13.5px;
            transition: var(--transition);
            position: relative;
            white-space: nowrap;
        }

        .sidebar-nav-link:hover {
            background: var(--sidebar-hover);
            color: #f1f5f9;
        }

        .sidebar-nav-link.active {
            background: var(--sidebar-active-bg);
            color: var(--secondary);
            font-weight: 600;
        }

        .sidebar-nav-link.active::before {
            content: '';
            position: absolute;
            left: -10px;
            top: 50%;
            transform: translateY(-50%);
            width: 3px;
            height: 24px;
            background: var(--secondary);
            border-radius: 0 3px 3px 0;
        }

        .sidebar-nav-icon {
            width: 20px;
            text-align: center;
            font-size: 15px;
            flex-shrink: 0;
        }

        .sidebar-nav-label {
            flex: 1;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .sidebar-badge {
            background: var(--danger);
            color: #fff;
            font-size: 10px;
            font-weight: 700;
            padding: 2px 7px;
            border-radius: 20px;
            min-width: 20px;
            text-align: center;
        }

        .sidebar-divider {
            height: 1px;
            background: rgba(255,255,255,0.06);
            margin: 8px 16px;
        }

        /* Sidebar danger links */
        .sidebar-nav-link.text-danger-soft {
            color: #fca5a5;
        }

        .sidebar-nav-link.text-danger-soft:hover {
            background: rgba(220, 38, 38, 0.12);
            color: #fca5a5;
        }

        /* Sidebar Footer */
        .sidebar-footer {
            padding: 16px;
            border-top: 1px solid rgba(255,255,255,0.06);
            flex-shrink: 0;
        }

        .sidebar-user {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .sidebar-avatar {
            width: 36px;
            height: 36px;
            background: linear-gradient(135deg, var(--secondary), var(--accent));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-weight: 700;
            font-size: 14px;
            flex-shrink: 0;
        }

        .sidebar-user-info {
            flex: 1;
            overflow: hidden;
        }

        .sidebar-user-name {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #f1f5f9;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .sidebar-user-role {
            display: block;
            font-size: 11px;
            color: var(--sidebar-heading);
        }

        /* Sidebar Overlay (mobile) */
        .sidebar-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.5);
            z-index: 1020;
            opacity: 0;
            transition: opacity 0.25s ease;
        }

        .sidebar-overlay.active {
            opacity: 1;
        }

        /* ============================================================
           MAIN CONTENT AREA
        ============================================================ */
        .admin-main {
            flex: 1;
            margin-left: var(--sidebar-width);
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            transition: var(--transition);
        }

        /* ============================================================
           TOP NAVIGATION BAR
        ============================================================ */
        .admin-topbar {
            background: var(--topbar-bg);
            border-bottom: 1px solid var(--border-color);
            height: var(--topbar-height);
            display: flex;
            align-items: center;
            padding: 0 24px;
            gap: 16px;
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0 1px 4px rgba(0,0,0,0.04);
        }

        .topbar-toggle {
            width: 36px;
            height: 36px;
            border: none;
            background: transparent;
            border-radius: var(--radius-sm);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            color: var(--text-secondary);
            font-size: 18px;
            transition: var(--transition);
            padding: 0;
        }

        .topbar-toggle:hover {
            background: var(--content-bg);
            color: var(--text-primary);
        }

        .topbar-breadcrumb {
            flex: 1;
        }

        .topbar-breadcrumb h1 {
            font-size: 18px;
            font-weight: 700;
            color: var(--text-primary);
            margin: 0;
            line-height: 1;
        }

        .topbar-breadcrumb p {
            font-size: 12px;
            color: var(--text-muted);
            margin: 2px 0 0;
        }

        .topbar-actions {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .topbar-icon-btn {
            width: 36px;
            height: 36px;
            border: 1px solid var(--border-color);
            background: var(--card-bg);
            border-radius: var(--radius-sm);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            color: var(--text-secondary);
            font-size: 15px;
            transition: var(--transition);
            text-decoration: none;
            position: relative;
        }

        .topbar-icon-btn:hover {
            background: var(--content-bg);
            color: var(--text-primary);
            border-color: #cbd5e1;
        }

        .topbar-notification-dot {
            position: absolute;
            top: 6px;
            right: 6px;
            width: 8px;
            height: 8px;
            background: var(--danger);
            border-radius: 50%;
            border: 2px solid #fff;
        }

        /* Topbar User Dropdown */
        .topbar-user {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 6px 10px;
            border: 1px solid var(--border-color);
            border-radius: var(--radius-sm);
            background: var(--card-bg);
            cursor: pointer;
            transition: var(--transition);
        }

        .topbar-user:hover {
            background: var(--content-bg);
        }

        .topbar-avatar {
            width: 32px;
            height: 32px;
            background: linear-gradient(135deg, var(--secondary), var(--accent));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-weight: 700;
            font-size: 13px;
        }

        .topbar-user-name {
            font-size: 13px;
            font-weight: 600;
            color: var(--text-primary);
        }

        /* ============================================================
           CONTENT AREA
        ============================================================ */
        .admin-content {
            flex: 1;
            padding: 24px;
            overflow-x: hidden;
        }

        /* ============================================================
           CARDS
        ============================================================ */
        .admin-card {
            background: var(--card-bg);
            border-radius: var(--radius-lg);
            box-shadow: var(--card-shadow);
            border: 1px solid var(--border-color);
            overflow: hidden;
            transition: var(--transition);
        }

        .admin-card:hover {
            box-shadow: var(--card-shadow-hover);
        }

        .admin-card-header {
            padding: 18px 24px;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }

        .admin-card-title {
            font-size: 15px;
            font-weight: 700;
            color: var(--text-primary);
            margin: 0;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .admin-card-title i {
            color: var(--secondary);
            font-size: 16px;
        }

        .admin-card-body {
            padding: 24px;
        }

        /* ============================================================
           STAT CARDS (Dashboard)
        ============================================================ */
        .stat-card {
            background: var(--card-bg);
            border-radius: var(--radius-lg);
            padding: 24px;
            border: 1px solid var(--border-color);
            box-shadow: var(--card-shadow);
            display: flex;
            align-items: flex-start;
            gap: 16px;
            transition: var(--transition);
            position: relative;
            overflow: hidden;
        }

        .stat-card::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: var(--stat-color, var(--secondary));
            transform: scaleX(0);
            transition: var(--transition);
        }

        .stat-card:hover {
            box-shadow: var(--card-shadow-hover);
            transform: translateY(-2px);
        }

        .stat-card:hover::after {
            transform: scaleX(1);
        }

        .stat-icon {
            width: 52px;
            height: 52px;
            border-radius: var(--radius-md);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            flex-shrink: 0;
        }

        .stat-info {
            flex: 1;
        }

        .stat-value {
            font-size: 28px;
            font-weight: 800;
            color: var(--text-primary);
            line-height: 1;
            margin-bottom: 4px;
        }

        .stat-label {
            font-size: 13px;
            color: var(--text-secondary);
            font-weight: 500;
        }

        .stat-change {
            font-size: 12px;
            margin-top: 6px;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .stat-change.up { color: var(--success); }
        .stat-change.down { color: var(--danger); }

        /* Stat card color variants */
        .stat-card-primary { --stat-color: var(--primary); }
        .stat-card-primary .stat-icon { background: rgba(26,54,93,0.1); color: var(--primary); }

        .stat-card-success { --stat-color: var(--success); }
        .stat-card-success .stat-icon { background: rgba(22,163,74,0.1); color: var(--success); }

        .stat-card-warning { --stat-color: var(--warning); }
        .stat-card-warning .stat-icon { background: rgba(217,119,6,0.1); color: var(--warning); }

        .stat-card-info { --stat-color: var(--secondary); }
        .stat-card-info .stat-icon { background: rgba(13,148,136,0.1); color: var(--secondary); }

        .stat-card-danger { --stat-color: var(--danger); }
        .stat-card-danger .stat-icon { background: rgba(220,38,38,0.1); color: var(--danger); }

        /* ============================================================
           BUTTONS
        ============================================================ */
        .btn {
            font-family: var(--font-main);
            font-weight: 600;
            font-size: 13.5px;
            border-radius: var(--radius-sm);
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .btn-primary {
            background: var(--primary);
            border-color: var(--primary);
        }

        .btn-primary:hover {
            background: var(--primary-hover);
            border-color: var(--primary-hover);
        }

        .btn-secondary {
            background: var(--secondary);
            border-color: var(--secondary);
            color: #fff;
        }

        .btn-secondary:hover {
            background: var(--secondary-hover);
            border-color: var(--secondary-hover);
            color: #fff;
        }

        .btn-sm {
            font-size: 12px;
            padding: 5px 12px;
        }

        .btn-xs {
            font-size: 11px;
            padding: 3px 8px;
            border-radius: 4px;
        }

        /* ============================================================
           FORMS
        ============================================================ */
        .form-label {
            font-weight: 600;
            font-size: 13px;
            color: var(--text-primary);
            margin-bottom: 6px;
        }

        .form-label .required {
            color: var(--danger);
            margin-left: 2px;
        }

        .form-control, .form-select {
            font-family: var(--font-main);
            font-size: 13.5px;
            border-radius: var(--radius-sm);
            border: 1.5px solid var(--border-color);
            color: var(--text-primary);
            padding: 9px 14px;
            transition: var(--transition);
            background-color: #fff;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--secondary);
            box-shadow: 0 0 0 3px rgba(13, 148, 136, 0.12);
            outline: none;
        }

        .form-control::placeholder {
            color: var(--text-muted);
        }

        .form-hint {
            font-size: 12px;
            color: var(--text-muted);
            margin-top: 4px;
        }

        .form-section-title {
            font-size: 14px;
            font-weight: 700;
            color: var(--text-secondary);
            margin-bottom: 16px;
            padding-bottom: 8px;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* ============================================================
           TABLES (DataTables)
        ============================================================ */
        .table {
            font-size: 13.5px;
            color: var(--text-primary);
        }

        .table thead th {
            font-weight: 700;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: var(--text-secondary);
            background: var(--content-bg);
            border-bottom: 2px solid var(--border-color);
            padding: 12px 16px;
            white-space: nowrap;
        }

        .table tbody td {
            padding: 12px 16px;
            vertical-align: middle;
            border-bottom: 1px solid var(--border-color);
        }

        .table tbody tr:last-child td {
            border-bottom: none;
        }

        .table tbody tr:hover td {
            background: #f8fafc;
        }

        .table-thumbnail {
            width: 50px;
            height: 38px;
            object-fit: cover;
            border-radius: var(--radius-sm);
            border: 1px solid var(--border-color);
        }

        /* DataTables override */
        .dataTables_wrapper .dataTables_filter input,
        .dataTables_wrapper .dataTables_length select {
            border: 1.5px solid var(--border-color);
            border-radius: var(--radius-sm);
            padding: 5px 10px;
            font-family: var(--font-main);
            font-size: 13px;
        }

        .dataTables_wrapper .dataTables_filter input:focus,
        .dataTables_wrapper .dataTables_length select:focus {
            border-color: var(--secondary);
            outline: none;
            box-shadow: 0 0 0 3px rgba(13, 148, 136, 0.12);
        }

        .dataTables_wrapper .dataTables_info {
            font-size: 12px;
            color: var(--text-muted);
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button {
            font-size: 13px;
            border-radius: var(--radius-sm) !important;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button.current {
            background: var(--primary) !important;
            border-color: var(--primary) !important;
            color: #fff !important;
        }

        /* ============================================================
           STATUS BADGES
        ============================================================ */
        .badge {
            font-size: 11px;
            font-weight: 600;
            padding: 4px 10px;
            border-radius: 20px;
            letter-spacing: 0.02em;
        }

        .badge-status-new { background: #dbeafe; color: #1d4ed8; }
        .badge-status-contacted { background: #cffafe; color: #0e7490; }
        .badge-status-follow_up { background: #fef3c7; color: #92400e; }
        .badge-status-negotiation { background: #ede9fe; color: #6d28d9; }
        .badge-status-closed_won { background: #dcfce7; color: #15803d; }
        .badge-status-closed_lost { background: #fee2e2; color: #991b1b; }
        .badge-status-published { background: #dcfce7; color: #15803d; }
        .badge-status-draft { background: #fef9c3; color: #854d0e; }

        .badge-priority-low { background: #f1f5f9; color: #475569; }
        .badge-priority-medium { background: #fef3c7; color: #92400e; }
        .badge-priority-high { background: #fee2e2; color: #991b1b; }

        /* ============================================================
           FLASH MESSAGES
        ============================================================ */
        .flash-container {
            position: fixed;
            top: 80px;
            right: 24px;
            z-index: 9999;
            display: flex;
            flex-direction: column;
            gap: 10px;
            max-width: 400px;
            width: calc(100% - 48px);
        }

        .flash-message {
            background: var(--card-bg);
            border-radius: var(--radius-md);
            padding: 14px 18px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.16);
            display: flex;
            align-items: flex-start;
            gap: 12px;
            border-left: 4px solid;
            animation: flashIn 0.35s ease forwards;
        }

        @keyframes flashIn {
            from { opacity: 0; transform: translateX(20px); }
            to { opacity: 1; transform: translateX(0); }
        }

        @keyframes flashOut {
            from { opacity: 1; transform: translateX(0); }
            to { opacity: 0; transform: translateX(20px); }
        }

        .flash-message.flash-out {
            animation: flashOut 0.3s ease forwards;
        }

        .flash-message.success { border-color: var(--success); }
        .flash-message.error { border-color: var(--danger); }
        .flash-message.warning { border-color: var(--warning); }
        .flash-message.info { border-color: var(--info); }

        .flash-icon {
            font-size: 16px;
            flex-shrink: 0;
            margin-top: 1px;
        }

        .flash-message.success .flash-icon { color: var(--success); }
        .flash-message.error .flash-icon { color: var(--danger); }
        .flash-message.warning .flash-icon { color: var(--warning); }
        .flash-message.info .flash-icon { color: var(--info); }

        .flash-content {
            flex: 1;
        }

        .flash-title {
            font-weight: 700;
            font-size: 13px;
            margin-bottom: 2px;
        }

        .flash-text {
            font-size: 12.5px;
            color: var(--text-secondary);
            line-height: 1.4;
        }

        .flash-close {
            background: none;
            border: none;
            color: var(--text-muted);
            cursor: pointer;
            font-size: 14px;
            padding: 0;
            flex-shrink: 0;
            transition: color 0.2s;
        }

        .flash-close:hover { color: var(--text-primary); }

        /* ============================================================
           PAGE HEADER (inside content)
        ============================================================ */
        .page-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 24px;
        }

        .page-header-left h2 {
            font-size: 22px;
            font-weight: 800;
            color: var(--text-primary);
            margin: 0 0 4px;
        }

        .page-header-left p {
            font-size: 13px;
            color: var(--text-muted);
            margin: 0;
        }

        .page-header-actions {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* ============================================================
           IMAGE PREVIEW
        ============================================================ */
        .img-preview-wrap {
            position: relative;
            display: inline-block;
        }

        .img-preview {
            width: 120px;
            height: 90px;
            object-fit: cover;
            border-radius: var(--radius-md);
            border: 2px solid var(--border-color);
            transition: var(--transition);
        }

        .img-preview:hover {
            border-color: var(--secondary);
        }

        .img-preview-large {
            width: 200px;
            height: 140px;
        }

        .img-remove-btn {
            position: absolute;
            top: -8px;
            right: -8px;
            width: 24px;
            height: 24px;
            background: var(--danger);
            color: #fff;
            border: none;
            border-radius: 50%;
            font-size: 11px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: var(--transition);
        }

        .img-remove-btn:hover {
            background: #b91c1c;
            transform: scale(1.1);
        }

        /* ============================================================
           TABS (Settings page)
        ============================================================ */
        .nav-tabs {
            border-bottom: 2px solid var(--border-color);
            gap: 4px;
        }

        .nav-tabs .nav-link {
            font-weight: 600;
            font-size: 13.5px;
            color: var(--text-secondary);
            border: none;
            border-radius: var(--radius-sm) var(--radius-sm) 0 0;
            padding: 10px 20px;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: var(--transition);
        }

        .nav-tabs .nav-link:hover {
            color: var(--text-primary);
            background: var(--content-bg);
        }

        .nav-tabs .nav-link.active {
            color: var(--secondary);
            background: var(--card-bg);
            border-bottom: 2px solid var(--secondary);
            margin-bottom: -2px;
        }

        /* ============================================================
           QUICK STATUS FILTER (Konsultasi)
        ============================================================ */
        .status-filter-tabs {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            margin-bottom: 20px;
        }

        .status-filter-btn {
            padding: 7px 14px;
            border-radius: 20px;
            border: 1.5px solid var(--border-color);
            background: var(--card-bg);
            font-size: 12.5px;
            font-weight: 600;
            color: var(--text-secondary);
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 6px;
            text-decoration: none;
        }

        .status-filter-btn:hover {
            border-color: var(--secondary);
            color: var(--secondary);
        }

        .status-filter-btn.active {
            background: var(--secondary);
            border-color: var(--secondary);
            color: #fff;
        }

        .status-filter-count {
            background: rgba(255,255,255,0.25);
            color: inherit;
            font-size: 11px;
            padding: 1px 7px;
            border-radius: 10px;
        }

        .status-filter-btn:not(.active) .status-filter-count {
            background: var(--content-bg);
        }

        /* ============================================================
           DETAIL VIEW
        ============================================================ */
        .detail-item {
            display: flex;
            flex-direction: column;
            gap: 2px;
            padding: 12px 0;
            border-bottom: 1px solid var(--border-color);
        }

        .detail-item:last-child {
            border-bottom: none;
        }

        .detail-label {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--text-muted);
        }

        .detail-value {
            font-size: 14px;
            font-weight: 500;
            color: var(--text-primary);
        }

        /* ============================================================
           TIMELINE (Notes)
        ============================================================ */
        .timeline {
            position: relative;
            padding-left: 28px;
        }

        .timeline::before {
            content: '';
            position: absolute;
            left: 8px;
            top: 4px;
            bottom: 0;
            width: 2px;
            background: var(--border-color);
        }

        .timeline-item {
            position: relative;
            margin-bottom: 16px;
        }

        .timeline-dot {
            position: absolute;
            left: -24px;
            top: 4px;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: var(--secondary);
            border: 2px solid #fff;
            box-shadow: 0 0 0 2px var(--border-color);
        }

        .timeline-content {
            background: var(--content-bg);
            border-radius: var(--radius-md);
            padding: 12px 16px;
            border: 1px solid var(--border-color);
        }

        .timeline-time {
            font-size: 11px;
            color: var(--text-muted);
            margin-bottom: 4px;
        }

        .timeline-text {
            font-size: 13px;
            color: var(--text-primary);
            line-height: 1.5;
        }

        /* ============================================================
           CHART CONTAINERS
        ============================================================ */
        .chart-container {
            position: relative;
            padding: 10px 0;
        }

        /* ============================================================
           QUICK ACTIONS
        ============================================================ */
        .quick-action-btn {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 14px 18px;
            background: var(--card-bg);
            border: 1.5px solid var(--border-color);
            border-radius: var(--radius-md);
            text-decoration: none;
            color: var(--text-primary);
            transition: var(--transition);
            font-weight: 600;
            font-size: 13.5px;
        }

        .quick-action-btn:hover {
            border-color: var(--secondary);
            color: var(--secondary);
            background: rgba(13, 148, 136, 0.04);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(13, 148, 136, 0.1);
        }

        .quick-action-icon {
            width: 40px;
            height: 40px;
            border-radius: var(--radius-sm);
            background: rgba(13, 148, 136, 0.1);
            color: var(--secondary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            flex-shrink: 0;
        }

        /* ============================================================
           EXPORT PAGE
        ============================================================ */
        .export-icon-card {
            text-align: center;
            padding: 32px 24px;
        }

        .export-icon {
            width: 64px;
            height: 64px;
            border-radius: var(--radius-lg);
            background: rgba(13, 148, 136, 0.1);
            color: var(--secondary);
            font-size: 28px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 16px;
        }

        /* ============================================================
           EMPTY STATE
        ============================================================ */
        .empty-state {
            text-align: center;
            padding: 48px 24px;
        }

        .empty-state-icon {
            font-size: 48px;
            color: var(--text-muted);
            margin-bottom: 16px;
            display: block;
        }

        .empty-state h4 {
            font-size: 18px;
            font-weight: 700;
            color: var(--text-secondary);
            margin-bottom: 8px;
        }

        .empty-state p {
            color: var(--text-muted);
            font-size: 13px;
            margin-bottom: 20px;
        }

        /* ============================================================
           COLOR PICKER PREVIEW
        ============================================================ */
        .color-preview-card {
            border-radius: var(--radius-lg);
            overflow: hidden;
            border: 1px solid var(--border-color);
        }

        .color-preview-bar {
            height: 8px;
            background: linear-gradient(90deg, var(--preview-primary, #1a365d), var(--preview-secondary, #0d9488), var(--preview-accent, #d97706));
        }

        .color-preview-body {
            padding: 20px;
            background: #fff;
        }

        .color-preview-btn {
            display: inline-block;
            padding: 8px 20px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 13px;
            color: #fff;
            background: var(--preview-primary, #1a365d);
            margin-right: 8px;
        }

        .color-preview-btn-sec {
            background: var(--preview-secondary, #0d9488);
        }

        .color-preview-btn-acc {
            background: var(--preview-accent, #d97706);
        }

        /* ============================================================
           RESPONSIVE
        ============================================================ */

        /* Tablet: collapse sidebar to icons */
        @media (max-width: 1024px) {
            .admin-sidebar {
                width: var(--sidebar-collapsed-width);
            }

            .admin-sidebar .sidebar-brand-text,
            .admin-sidebar .sidebar-nav-label,
            .admin-sidebar .sidebar-section-title,
            .admin-sidebar .sidebar-badge,
            .admin-sidebar .sidebar-user-info {
                display: none;
            }

            .admin-sidebar .sidebar-brand {
                justify-content: center;
                padding: 20px 8px;
            }

            .admin-sidebar .sidebar-nav-item {
                margin: 2px 6px;
            }

            .admin-sidebar .sidebar-nav-link {
                justify-content: center;
                padding: 10px 8px;
            }

            .admin-sidebar .sidebar-nav-link.active::before {
                display: none;
            }

            .admin-sidebar .sidebar-footer {
                padding: 12px 6px;
            }

            .admin-sidebar .sidebar-user {
                justify-content: center;
            }

            .admin-main {
                margin-left: var(--sidebar-collapsed-width);
            }
        }

        /* Mobile: sidebar as drawer */
        @media (max-width: 768px) {
            .admin-sidebar {
                width: var(--sidebar-width);
                transform: translateX(-100%);
            }

            .admin-sidebar.mobile-open {
                transform: translateX(0);
            }

            .admin-sidebar .sidebar-brand-text,
            .admin-sidebar .sidebar-nav-label,
            .admin-sidebar .sidebar-section-title,
            .admin-sidebar .sidebar-badge,
            .admin-sidebar .sidebar-user-info {
                display: block;
            }

            .admin-sidebar .sidebar-brand {
                justify-content: flex-start;
                padding: 20px 16px;
            }

            .admin-sidebar .sidebar-nav-item {
                margin: 2px 10px;
            }

            .admin-sidebar .sidebar-nav-link {
                justify-content: flex-start;
                padding: 10px 12px;
            }

            .admin-sidebar .sidebar-nav-link.active::before {
                display: block;
            }

            .admin-sidebar .sidebar-footer {
                padding: 16px;
            }

            .admin-sidebar .sidebar-user {
                justify-content: flex-start;
            }

            .admin-main {
                margin-left: 0;
            }

            .sidebar-overlay {
                display: block;
            }

            .admin-content {
                padding: 16px;
            }

            .topbar-user-name {
                display: none;
            }

            .page-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 12px;
            }
        }

        /* ============================================================
           MISCELLANEOUS UTILITIES
        ============================================================ */
        .text-primary-custom { color: var(--primary); }
        .text-secondary-custom { color: var(--secondary); }
        .text-accent { color: var(--accent); }
        .bg-primary-custom { background: var(--primary); }
        .bg-secondary-custom { background: var(--secondary); }

        .fw-700 { font-weight: 700; }
        .fw-800 { font-weight: 800; }

        .rounded-custom { border-radius: var(--radius-md); }

        .overdue-row td { background: #fff8f0 !important; }
        .overdue-row:hover td { background: #fff3e0 !important; }

        /* Tooltip for collapsed sidebar menu items */
        @media (max-width: 1024px) and (min-width: 769px) {
            .sidebar-nav-link {
                position: relative;
            }

            .sidebar-nav-link .sidebar-nav-label {
                position: absolute;
                left: calc(100% + 12px);
                top: 50%;
                transform: translateY(-50%);
                background: #0f172a;
                color: #f1f5f9;
                padding: 6px 12px;
                border-radius: var(--radius-sm);
                font-size: 12px;
                white-space: nowrap;
                pointer-events: none;
                opacity: 0;
                display: block !important;
                transition: opacity 0.2s ease;
                box-shadow: 0 4px 12px rgba(0,0,0,0.3);
            }

            .sidebar-nav-link:hover .sidebar-nav-label {
                opacity: 1;
            }
        }

        /* Content editor toolbar */
        .editor-toolbar {
            display: flex;
            flex-wrap: wrap;
            gap: 4px;
            padding: 8px 12px;
            background: var(--content-bg);
            border: 1.5px solid var(--border-color);
            border-bottom: none;
            border-radius: var(--radius-sm) var(--radius-sm) 0 0;
        }

        .editor-toolbar .toolbar-btn {
            width: 32px;
            height: 32px;
            border: 1px solid var(--border-color);
            background: var(--card-bg);
            border-radius: 4px;
            font-size: 13px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-primary);
            font-weight: 600;
            transition: var(--transition);
        }

        .editor-toolbar .toolbar-btn:hover {
            background: var(--secondary);
            color: #fff;
            border-color: var(--secondary);
        }

        .editor-toolbar .toolbar-sep {
            width: 1px;
            background: var(--border-color);
            margin: 4px 4px;
        }

        .editor-textarea {
            border-radius: 0 0 var(--radius-sm) var(--radius-sm) !important;
            min-height: 350px;
            resize: vertical;
            font-family: 'Courier New', monospace;
            font-size: 13px;
        }

        /* Row action buttons */
        .action-btns {
            display: flex;
            gap: 4px;
            flex-wrap: nowrap;
        }

        /* Breadcrumb styling */
        .breadcrumb {
            font-size: 12px;
            margin: 0;
            padding: 0;
            background: transparent;
        }

        .breadcrumb-item a {
            color: var(--text-secondary);
            text-decoration: none;
        }

        .breadcrumb-item.active {
            color: var(--text-muted);
        }

        /* Alert override */
        .alert {
            border-radius: var(--radius-md);
            border: none;
            font-size: 13.5px;
        }

        /* Select2-like select */
        .form-select {
            padding-right: 32px;
        }

        /* Loading spinner overlay */
        .loading-overlay {
            position: fixed;
            inset: 0;
            background: rgba(255,255,255,0.8);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9998;
            backdrop-filter: blur(2px);
        }

        .loading-spinner {
            width: 48px;
            height: 48px;
            border: 4px solid var(--border-color);
            border-top-color: var(--secondary);
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* ============================================================
           ADMIN FOOTER
        ============================================================ */
        .admin-footer {
            background: var(--card-bg);
            border-top: 1px solid var(--border-color);
            padding: 14px 24px;
            font-size: 12px;
            color: var(--text-muted);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
    </style>
</head>
<body>
<!-- Flash Messages Container -->
<?php
$flash = function_exists('getFlash') ? getFlash() : [];
if (!empty($flash)): ?>
<div class="flash-container" id="flashContainer">
    <?php foreach ($flash as $f):
        $icons = ['success' => 'fa-check-circle', 'error' => 'fa-times-circle', 'warning' => 'fa-exclamation-triangle', 'info' => 'fa-info-circle'];
        $titles = ['success' => 'Berhasil', 'error' => 'Gagal', 'warning' => 'Perhatian', 'info' => 'Informasi'];
        $type = $f['type'] ?? 'info';
        $icon = $icons[$type] ?? 'fa-info-circle';
        $title = $titles[$type] ?? 'Info';
    ?>
    <div class="flash-message <?= htmlspecialchars($type) ?>">
        <i class="fas <?= $icon ?> flash-icon"></i>
        <div class="flash-content">
            <div class="flash-title"><?= htmlspecialchars($title) ?></div>
            <div class="flash-text"><?= htmlspecialchars($f['message']) ?></div>
        </div>
        <button class="flash-close" onclick="dismissFlash(this)"><i class="fas fa-times"></i></button>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- Admin Sidebar Overlay (mobile) -->
<div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>

<!-- Admin Layout Wrapper -->
<div class="admin-wrapper">
