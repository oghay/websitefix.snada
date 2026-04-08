<?php
/**
 * Core Functions - OJS Developer Indonesia
 * All helper functions for the admin panel and frontend.
 *
 * Note: This is a stub/placeholder file created by the admin builder.
 * The full implementation is created by the main site builder agent.
 * The admin panel depends on all functions listed below.
 */

// ============================================================
// PDO Singleton
// ============================================================

function db(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $host    = defined('DB_HOST')    ? DB_HOST    : 'localhost';
        $dbname  = defined('DB_NAME')    ? DB_NAME    : 'ojs_developer';
        $user    = defined('DB_USER')    ? DB_USER    : 'root';
        $pass    = defined('DB_PASS')    ? DB_PASS    : '';
        $charset = defined('DB_CHARSET') ? DB_CHARSET : 'utf8mb4';

        $dsn = "mysql:host={$host};dbname={$dbname};charset={$charset}";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $pdo = new PDO($dsn, $user, $pass, $options);
        } catch (PDOException $e) {
            error_log('DB connection error: ' . $e->getMessage());
            die('<div style="font-family:sans-serif;padding:40px;text-align:center;"><h2 style="color:#dc2626;">Koneksi Database Gagal</h2><p>Periksa konfigurasi database di config.php.</p></div>');
        }
    }
    return $pdo;
}

// ============================================================
// Query Helpers
// ============================================================

function query(string $sql, array $params = []): PDOStatement {
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return $stmt;
}

function fetch(string $sql, array $params = []) {
    return query($sql, $params)->fetch();
}

function fetchAll(string $sql, array $params = []): array {
    return query($sql, $params)->fetchAll();
}

function insert(string $table, array $data) {
    $cols = implode(', ', array_map(fn($k) => "`{$k}`", array_keys($data)));
    $plcr = implode(', ', array_fill(0, count($data), '?'));
    query("INSERT INTO `{$table}` ({$cols}) VALUES ({$plcr})", array_values($data));
    return db()->lastInsertId();
}

function update(string $table, array $data, string $where, array $where_params = []): int {
    $set = implode(', ', array_map(fn($k) => "`{$k}` = ?", array_keys($data)));
    $stmt = query("UPDATE `{$table}` SET {$set} WHERE {$where}", array_merge(array_values($data), $where_params));
    return $stmt->rowCount();
}

function delete(string $table, string $where, array $params = []): int {
    return query("DELETE FROM `{$table}` WHERE {$where}", $params)->rowCount();
}

// ============================================================
// Settings
// ============================================================

function getSetting(string $key, string $default = ''): string {
    static $cache = [];
    if (!isset($cache[$key])) {
        try {
            $row = fetch("SELECT setting_value FROM settings WHERE setting_key = ?", [$key]);
            $cache[$key] = $row ? ($row['setting_value'] ?? $default) : $default;
        } catch (Exception $e) {
            $cache[$key] = $default;
        }
    }
    return $cache[$key];
}

function setSetting(string $key, string $value): void {
    try {
        $exists = fetch("SELECT id FROM settings WHERE setting_key = ?", [$key]);
        if ($exists) {
            update('settings', ['setting_value' => $value], 'setting_key = ?', [$key]);
        } else {
            insert('settings', ['setting_key' => $key, 'setting_value' => $value]);
        }
        // Clear cache
        // (static cache will be stale, acceptable for single-request lifecycle)
    } catch (Exception $e) {
        // silently fail or log
    }
}

function getAllSettings(): array {
    try {
        $rows = fetchAll("SELECT setting_key, setting_value FROM settings");
        $out = [];
        foreach ($rows as $row) {
            $out[$row['setting_key']] = $row['setting_value'];
        }
        return $out;
    } catch (Exception $e) {
        return [];
    }
}

// ============================================================
// Helpers
// ============================================================

function slugify(string $text): string {
    $text = mb_strtolower($text, 'UTF-8');
    // Transliterate common Indonesian/Latin characters
    $replace = [
        'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a', 'å' => 'a',
        'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e',
        'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i',
        'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'o',
        'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ü' => 'u',
        'ñ' => 'n', 'ç' => 'c',
    ];
    $text = strtr($text, $replace);
    $text = preg_replace('/[^a-z0-9\s\-]/', '', $text);
    $text = preg_replace('/[\s\-]+/', '-', $text);
    return trim($text, '-');
}

function uploadImage(array $file, string $folder) {
    $upload_dir = dirname(__DIR__) . '/assets/uploads/' . $folder . '/';

    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    // SVG removed – can embed JavaScript (XSS risk)
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $allowed_ext   = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    $mime = mime_content_type($file['tmp_name']);
    $ext  = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if (!in_array($mime, $allowed_types) || !in_array($ext, $allowed_ext)) {
        return false;
    }

    if ($file['size'] > 2 * 1024 * 1024) {
        return false;
    }

    $filename = uniqid('img_', true) . '.' . $ext;
    $dest     = $upload_dir . $filename;

    if (move_uploaded_file($file['tmp_name'], $dest)) {
        return $filename;
    }

    return false;
}

function deleteImage(string $path): bool {
    $full_path = dirname(__DIR__) . '/assets/uploads/' . $path;
    if (file_exists($full_path) && is_file($full_path)) {
        return unlink($full_path);
    }
    return false;
}

function truncate(string $text, int $length = 150): string {
    $text = strip_tags($text);
    if (mb_strlen($text) <= $length) return $text;
    return mb_substr($text, 0, $length) . '...';
}

function formatDate(string $date, string $format = 'd M Y'): string {
    if (empty($date)) return '-';
    return date($format, strtotime($date));
}

// ============================================================
// Auth
// ============================================================

function isLoggedIn(): bool {
    return !empty($_SESSION['logged_in']) && !empty($_SESSION['admin_id']);
}

function redirect(string $url): void {
    header('Location: ' . $url);
    exit;
}

// ============================================================
// CSRF
// ============================================================

function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf(string $token): bool {
    return !empty($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// ============================================================
// Flash Messages
// ============================================================

function flash(string $type, string $message): void {
    if (!isset($_SESSION['flash_messages'])) {
        $_SESSION['flash_messages'] = [];
    }
    $_SESSION['flash_messages'][] = ['type' => $type, 'message' => $message];
}

function getFlash(): array {
    $messages = $_SESSION['flash_messages'] ?? [];
    unset($_SESSION['flash_messages']);
    return $messages;
}

// ============================================================
// Sanitize
// ============================================================

function sanitize(string $input): string {
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}

// ============================================================
// Pagination
// ============================================================

function getPagination(int $total, int $perPage, int $currentPage): array {
    $totalPages  = (int)ceil($total / $perPage);
    $currentPage = max(1, min($currentPage, $totalPages));
    $offset      = ($currentPage - 1) * $perPage;

    return [
        'total'       => $total,
        'per_page'    => $perPage,
        'current'     => $currentPage,
        'total_pages' => $totalPages,
        'offset'      => $offset,
        'has_prev'    => $currentPage > 1,
        'has_next'    => $currentPage < $totalPages,
        'prev'        => $currentPage - 1,
        'next'        => $currentPage + 1,
    ];
}

// ============================================================
// Status Badges (Bootstrap class names)
// ============================================================

function getStatusBadge(string $status): string {
    $map = [
        'new'         => 'primary',
        'contacted'   => 'info',
        'follow_up'   => 'warning',
        'negotiation' => 'secondary',
        'closed_won'  => 'success',
        'closed_lost' => 'danger',
        'published'   => 'success',
        'draft'       => 'warning',
    ];
    return $map[$status] ?? 'secondary';
}

function getStatusLabel(string $status): string {
    $map = [
        'new'         => 'Baru',
        'contacted'   => 'Dihubungi',
        'follow_up'   => 'Follow Up',
        'negotiation' => 'Negosiasi',
        'closed_won'  => 'Closed Won',
        'closed_lost' => 'Closed Lost',
        'published'   => 'Publikasi',
        'draft'       => 'Draft',
    ];
    return $map[$status] ?? ucfirst(str_replace('_', ' ', $status));
}

function getPriorityBadge(string $priority): string {
    $map = [
        'low'    => 'secondary',
        'medium' => 'warning',
        'high'   => 'danger',
    ];
    return $map[$priority] ?? 'secondary';
}

// ============================================================
// Order Tracking
// ============================================================

/**
 * Generate tracking code: SNADA-ddMMyyyy-NNN
 */
function generateTrackingCode(): string {
    $dateStr = date('dmY'); // e.g., 06042026
    $prefix  = 'SNADA-' . $dateStr . '-';

    // Count existing orders with this date prefix
    $row = fetch(
        "SELECT COUNT(*) as cnt FROM orders WHERE tracking_code LIKE ?",
        [$prefix . '%']
    );
    $nextNum = ($row ? (int)$row['cnt'] : 0) + 1;

    return $prefix . str_pad($nextNum, 3, '0', STR_PAD_LEFT);
}

/**
 * Default milestones for a new order.
 * Returns array of [title, description].
 */
function getDefaultMilestones(string $serviceType = 'setup_ojs'): array {
    $base = [
        ['Pesanan Dibuat',                'Pesanan telah diterima dan sedang diproses oleh tim kami.'],
        ['Persiapan & Audit Awal',        'Audit kondisi server, domain, dan data yang tersedia.'],
        ['Instalasi Server & OJS',        'Instalasi OJS versi terbaru beserta konfigurasi server, database, dan SSL.'],
        ['Konfigurasi OJS',               'Pengaturan alur editorial, peran pengguna, email SMTP, dan plugin.'],
        ['Setup Tema & Tampilan',         'Pemasangan dan kustomisasi tema sesuai identitas institusi.'],
        ['Import Data Jurnal',            'Migrasi/import artikel, metadata, pengguna, dan edisi jurnal.'],
        ['Konfigurasi Email & Notifikasi','Pengaturan template email, notifikasi otomatis, dan reminder.'],
        ['SEO & Indexing',                'Optimasi metadata, sitemap, Google Scholar, dan pendaftaran DOI.'],
        ['Testing & Quality Assurance',   'Pengujian menyeluruh: fungsional, responsif, keamanan, dan performa.'],
        ['Serah Terima & Pelatihan',      'Dokumentasi lengkap, pelatihan tim editorial, dan garansi support.'],
    ];

    return $base;
}

function getOrderStatusLabel(string $status): string {
    $map = [
        'pending'     => 'Menunggu',
        'in_progress' => 'Dikerjakan',
        'completed'   => 'Selesai',
        'cancelled'   => 'Dibatalkan',
    ];
    return $map[$status] ?? ucfirst($status);
}

function getOrderStatusBadge(string $status): string {
    $map = [
        'pending'     => 'warning',
        'in_progress' => 'info',
        'completed'   => 'success',
        'cancelled'   => 'danger',
    ];
    return $map[$status] ?? 'secondary';
}

function getMilestoneStatusLabel(string $status): string {
    $map = [
        'pending'     => 'Menunggu',
        'in_progress' => 'Sedang Dikerjakan',
        'completed'   => 'Selesai',
    ];
    return $map[$status] ?? ucfirst($status);
}

// ============================================================
// WhatsApp Notification
// ============================================================

/**
 * Send WhatsApp notification via configured API provider.
 * Supported providers: fonnte, wablas, custom.
 * Returns ['success' => bool, 'message' => string]
 */
function sendWhatsAppNotification(string $to, string $message): array {
    $token = WA_API_TOKEN; // Use token from environment variable
    if (empty($token)) {
        return ["success" => false, "message" => "WhatsApp API Token not configured."];
    }

    $enabled  = getSetting('wa_notif_enabled', '0');
    if ($enabled !== '1') {
        return ['success' => false, 'message' => 'Notifikasi WA dinonaktifkan.'];
    }

    $provider = getSetting('wa_api_provider', 'fonnte');
    // $token is already defined from WA_API_TOKEN constant
    // if (empty($token)) {
    //     return ['success' => false, 'message' => 'API token belum dikonfigurasi.'];
    // }

    // Normalize phone: remove spaces, dashes, leading 0, ensure starts with 62
    $phone = preg_replace('/[\s\-\(\)]/', '', $phone);
    if (str_starts_with($phone, '+')) $phone = substr($phone, 1);
    if (str_starts_with($phone, '0'))  $phone = '62' . substr($phone, 1);
    if (!str_starts_with($phone, '62')) $phone = '62' . $phone;

    $result = ['success' => false, 'message' => ''];

    try {
        switch ($provider) {
            case 'fonnte':
                $result = _sendViaFonnte($phone, $message, $token);
                break;
            case 'wablas':
                $result = _sendViaWablas($phone, $message, $token);
                break;
            case 'custom':
                $customUrl = getSetting('wa_api_url', '');
                if (empty($customUrl)) {
                    return ['success' => false, 'message' => 'URL API custom belum diisi.'];
                }
                $result = _sendViaCustom($phone, $message, $token, $customUrl);
                break;
            default:
                $result = ['success' => false, 'message' => 'Provider tidak dikenali.'];
        }
    } catch (Exception $e) {
        $result = ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
    }

    // Log notification
    _logNotification($phone, $message, $provider, $result);

    return $result;
}

function _sendViaFonnte(string $phone, string $message, string $token): array {
    $ch = curl_init('https://api.fonnte.com/send');
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => http_build_query([
            'target'      => $phone,
            'message'     => $message,
            'countryCode' => '62',
        ]),
        CURLOPT_HTTPHEADER     => ['Authorization: ' . $token],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 15,
        CURLOPT_SSL_VERIFYPEER => true,
    ]);
    $resp = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err  = curl_error($ch);
    curl_close($ch);

    if ($err) return ['success' => false, 'message' => 'cURL error: ' . $err];

    $json = json_decode($resp, true);
    if ($code === 200 && isset($json['status']) && $json['status'] === true) {
        return ['success' => true, 'message' => 'Terkirim via Fonnte.'];
    }
    return ['success' => false, 'message' => 'Fonnte: ' . ($json['reason'] ?? $json['message'] ?? $resp)];
}

function _sendViaWablas(string $phone, string $message, string $token): array {
    $ch = curl_init('https://pati.wablas.com/api/send-message');
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => http_build_query([
            'phone'   => $phone,
            'message' => $message,
        ]),
        CURLOPT_HTTPHEADER     => ['Authorization: ' . $token],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 15,
        CURLOPT_SSL_VERIFYPEER => true,
    ]);
    $resp = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err  = curl_error($ch);
    curl_close($ch);

    if ($err) return ['success' => false, 'message' => 'cURL error: ' . $err];

    $json = json_decode($resp, true);
    if ($code === 200 && isset($json['status']) && $json['status'] === true) {
        return ['success' => true, 'message' => 'Terkirim via Wablas.'];
    }
    return ['success' => false, 'message' => 'Wablas: ' . ($json['message'] ?? $resp)];
}

function _sendViaCustom(string $phone, string $message, string $token, string $url): array {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => json_encode([
            'phone'   => $phone,
            'message' => $message,
        ]),
        CURLOPT_HTTPHEADER     => [
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json',
        ],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 15,
        CURLOPT_SSL_VERIFYPEER => true,
    ]);
    $resp = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err  = curl_error($ch);
    curl_close($ch);

    if ($err) return ['success' => false, 'message' => 'cURL error: ' . $err];
    if ($code >= 200 && $code < 300) {
        return ['success' => true, 'message' => 'Terkirim via Custom API.'];
    }
    return ['success' => false, 'message' => 'HTTP ' . $code . ': ' . substr($resp, 0, 200)];
}

function _logNotification(string $phone, string $message, string $provider, array $result): void {
    try {
        insert('notification_logs', [
            'channel'    => 'whatsapp',
            'provider'   => $provider,
            'recipient'  => $phone,
            'message'    => mb_substr($message, 0, 2000),
            'status'     => $result['success'] ? 'sent' : 'failed',
            'response'   => mb_substr($result['message'] ?? '', 0, 500),
        ]);
    } catch (Exception $e) {
        error_log('Failed to log notification: ' . $e->getMessage());
    }
}

/**
 * Build WhatsApp message for milestone update from template.
 * Placeholders: {tracking_code}, {client_name}, {milestone}, {status}, {progress}, {site_name}, {tracking_url}
 */
function buildMilestoneNotifMessage(array $order, string $milestoneTitle, string $newStatus): string {
    $defaultTemplate = "Halo {client_name}! 👋\n\n"
        . "Update pesanan Anda (*{tracking_code}*):\n\n"
        . "📌 *{milestone}*\n"
        . "Status: *{status}*\n"
        . "Progres: {progress}%\n\n"
        . "Pantau detail di:\n{tracking_url}\n\n"
        . "_{site_name}_";

    $template = getSetting('wa_notif_template', $defaultTemplate);
    if (empty(trim($template))) $template = $defaultTemplate;

    // Calculate progress
    $totalMs = 0;
    $completedMs = 0;
    try {
        $milestones = fetchAll(
            "SELECT status FROM order_milestones WHERE order_id = ?",
            [$order['id']]
        );
        $totalMs = count($milestones);
        foreach ($milestones as $ms) {
            if ($ms['status'] === 'completed') $completedMs++;
        }
    } catch (Exception $e) {}
    $progressPct = $totalMs > 0 ? round(($completedMs / $totalMs) * 100) : 0;

    // Build tracking URL
    $siteUrl = defined('SITE_URL') ? SITE_URL : '';
    $trackingUrl = $siteUrl . '/tracking?code=' . urlencode($order['tracking_code']);

    $replacements = [
        '{tracking_code}' => $order['tracking_code'],
        '{client_name}'   => $order['client_name'],
        '{milestone}'     => $milestoneTitle,
        '{status}'        => getMilestoneStatusLabel($newStatus),
        '{progress}'      => (string)$progressPct,
        '{site_name}'     => getSetting('site_name', 'OJS Developer Indonesia'),
        '{tracking_url}'  => $trackingUrl,
    ];

    return str_replace(array_keys($replacements), array_values($replacements), $template);
}

// ============================================================
// Navigation Helper
// ============================================================

function activeClass(string $page, string $current): string {
    // Also match detail pages (e.g. portofolio-detail matches portofolio)
    if ($page === $current) return 'active';
    if (strpos($current, $page . '-') === 0) return 'active';
    return '';
}
