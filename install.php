<?php
/**
 * OJS Developer Website - Web Installer
 * Standalone installer that doesn't require config.php to exist.
 */

// Prevent running if already installed
if (file_exists(__DIR__ . '/config.php')) {
    header('Location: index.php');
    exit;
}

$step = isset($_GET['step']) ? (int)$_GET['step'] : 1;
$errors = [];
$success = false;

// ──────────────────────────────────────────
// PROCESS INSTALLATION (POST)
// ──────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $step === 2) {
    $dbHost   = trim($_POST['db_host'] ?? 'localhost');
    $dbName   = trim($_POST['db_name'] ?? '');
    $dbUser   = trim($_POST['db_user'] ?? '');
    $dbPass   = $_POST['db_pass'] ?? '';
    $siteUrl  = rtrim(trim($_POST['site_url'] ?? ''), '/');
    $adminUser = trim($_POST['admin_user'] ?? '');
    $adminPass = $_POST['admin_pass'] ?? '';
    $adminName = trim($_POST['admin_name'] ?? '');
    $adminEmail= trim($_POST['admin_email'] ?? '');

    // Validate
    if (empty($dbHost))    $errors[] = 'Host database wajib diisi.';
    if (empty($dbName))    $errors[] = 'Nama database wajib diisi.';
    if (empty($dbUser))    $errors[] = 'Username database wajib diisi.';
    if (empty($siteUrl))   $errors[] = 'URL situs wajib diisi.';
    if (empty($adminUser)) $errors[] = 'Username admin wajib diisi.';
    if (strlen($adminPass) < 8) $errors[] = 'Password admin minimal 8 karakter.';
    if (empty($adminName)) $errors[] = 'Nama admin wajib diisi.';

    if (empty($errors)) {
        // Test DB connection
        try {
            $dsn = "mysql:host={$dbHost};charset=utf8mb4";
            $testPdo = new PDO($dsn, $dbUser, $dbPass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            ]);
        } catch (PDOException $e) {
            $errors[] = 'Koneksi database gagal: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
        }

        if (empty($errors)) {
            try {
                // Create DB if not exists
                $testPdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                $testPdo->exec("USE `{$dbName}`");

                // Create tables
                $sql = "
                    CREATE TABLE IF NOT EXISTS admins (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        username VARCHAR(50) UNIQUE NOT NULL,
                        password VARCHAR(255) NOT NULL,
                        name VARCHAR(100) NOT NULL,
                        email VARCHAR(255),
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

                    CREATE TABLE IF NOT EXISTS settings (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        setting_key VARCHAR(100) UNIQUE NOT NULL,
                        setting_value TEXT,
                        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

                    CREATE TABLE IF NOT EXISTS portfolio (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        title VARCHAR(255) NOT NULL,
                        slug VARCHAR(255) UNIQUE NOT NULL,
                        description TEXT,
                        image VARCHAR(255),
                        client_name VARCHAR(255),
                        client_institution VARCHAR(255),
                        website_url VARCHAR(500),
                        category ENUM('jurnal','konferensi','repositori','lainnya') DEFAULT 'jurnal',
                        is_featured TINYINT(1) DEFAULT 0,
                        status ENUM('draft','published') DEFAULT 'published',
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

                    CREATE TABLE IF NOT EXISTS blog_posts (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        title VARCHAR(255) NOT NULL,
                        slug VARCHAR(255) UNIQUE NOT NULL,
                        excerpt TEXT,
                        content LONGTEXT,
                        image VARCHAR(255),
                        author VARCHAR(100) DEFAULT 'Admin',
                        status ENUM('draft','published') DEFAULT 'draft',
                        category VARCHAR(50) DEFAULT '',
                        views INT DEFAULT 0,
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

                    CREATE TABLE IF NOT EXISTS orders (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        tracking_code VARCHAR(30) UNIQUE NOT NULL,
                        client_name VARCHAR(255) NOT NULL,
                        client_email VARCHAR(255),
                        client_phone VARCHAR(50),
                        client_institution VARCHAR(255),
                        service_type VARCHAR(100) DEFAULT 'setup_ojs',
                        package_tier VARCHAR(50) DEFAULT '',
                        description TEXT,
                        status ENUM('pending','in_progress','completed','cancelled') DEFAULT 'pending',
                        notes TEXT,
                        price BIGINT DEFAULT 0,
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

                    CREATE TABLE IF NOT EXISTS order_milestones (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        order_id INT NOT NULL,
                        title VARCHAR(255) NOT NULL,
                        description TEXT,
                        status ENUM('pending','in_progress','completed') DEFAULT 'pending',
                        completed_at DATETIME NULL,
                        sort_order INT DEFAULT 0,
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

                    CREATE TABLE IF NOT EXISTS notification_logs (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        channel VARCHAR(30) NOT NULL DEFAULT 'whatsapp',
                        provider VARCHAR(50) NOT NULL DEFAULT 'fonnte',
                        recipient VARCHAR(50) NOT NULL,
                        message TEXT,
                        status ENUM('sent','failed') DEFAULT 'failed',
                        response VARCHAR(500),
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

                    CREATE TABLE IF NOT EXISTS consultations (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        name VARCHAR(255) NOT NULL,
                        email VARCHAR(255) NOT NULL,
                        phone VARCHAR(50),
                        institution VARCHAR(255),
                        service_type ENUM('setup_ojs','migrasi','kustomisasi','pelatihan','maintenance','lainnya') DEFAULT 'setup_ojs',
                        budget_range VARCHAR(100),
                        message TEXT,
                        status ENUM('new','contacted','follow_up','negotiation','closed_won','closed_lost') DEFAULT 'new',
                        priority ENUM('low','medium','high') DEFAULT 'medium',
                        notes TEXT,
                        follow_up_date DATE NULL,
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
                ";

                // Execute each statement separately
                foreach (array_filter(array_map('trim', explode(';', $sql))) as $stmt) {
                    if (!empty($stmt)) {
                        $testPdo->exec($stmt);
                    }
                }

                // Insert default settings
                $defaultSettings = [
                    'site_name'        => 'OJS Developer Indonesia',
                    'site_tagline'     => 'Jasa Pembuatan & Pengelolaan Website Jurnal OJS Profesional',
                    'primary_color'    => '#1a365d',
                    'secondary_color'  => '#0d9488',
                    'accent_color'     => '#d97706',
                    'logo_path'        => '',
                    'favicon_path'     => '',
                    'whatsapp_number'  => '',
                    'email_contact'    => $adminEmail,
                    'address'          => '',
                    'footer_text'      => '© ' . date('Y') . ' OJS Developer Indonesia. All rights reserved.',
                    'meta_description' => 'Jasa pembuatan website jurnal OJS profesional untuk perguruan tinggi, lembaga penelitian, dan organisasi akademik di Indonesia.',
                ];

                $insertSettings = $testPdo->prepare(
                    "INSERT IGNORE INTO settings (setting_key, setting_value) VALUES (?, ?)"
                );
                foreach ($defaultSettings as $key => $value) {
                    $insertSettings->execute([$key, $value]);
                }

                // Create admin account
                $hashedPassword = password_hash($adminPass, PASSWORD_BCRYPT);
                $insertAdmin = $testPdo->prepare(
                    "INSERT IGNORE INTO admins (username, password, name, email) VALUES (?, ?, ?, ?)"
                );
                $insertAdmin->execute([$adminUser, $hashedPassword, $adminName, $adminEmail]);

                // Generate config.php
                $configContent = "<?php\n";
                $configContent .= "define('DB_HOST', " . var_export($dbHost, true) . ");\n";
                $configContent .= "define('DB_NAME', " . var_export($dbName, true) . ");\n";
                $configContent .= "define('DB_USER', " . var_export($dbUser, true) . ");\n";
                $configContent .= "define('DB_PASS', " . var_export($dbPass, true) . ");\n";
                $configContent .= "define('SITE_URL', " . var_export($siteUrl, true) . ");\n";
                $configContent .= "define('UPLOAD_MAX_SIZE', 2 * 1024 * 1024);\n";
                $configContent .= "define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/webp', 'image/gif']);\n";

                if (file_put_contents(__DIR__ . '/config.php', $configContent) === false) {
                    $errors[] = 'Gagal membuat file config.php. Pastikan PHP memiliki izin menulis di direktori ini.';
                } else {
                    // Create upload directories
                    $dirs = [
                        __DIR__ . '/assets/uploads/portfolio',
                        __DIR__ . '/assets/uploads/blog',
                        __DIR__ . '/assets/uploads/site',
                    ];
                    foreach ($dirs as $dir) {
                        if (!is_dir($dir)) {
                            mkdir($dir, 0755, true);
                        }
                    }
                    $success = true;
                }

            } catch (PDOException $e) {
                $errors[] = 'Kesalahan database: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
            }
        }
    }
}

// ──────────────────────────────────────────
// Guess site URL
// ──────────────────────────────────────────
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$scriptDir = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
$guessedUrl = $protocol . '://' . $host . $scriptDir;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instalasi - OJS Developer Indonesia</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&family=DM+Serif+Display&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        :root {
            --primary: #1a365d;
            --secondary: #0d9488;
            --accent: #d97706;
        }
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: linear-gradient(135deg, #1a365d 0%, #0d9488 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
        }
        .install-card {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.2);
            max-width: 600px;
            width: 100%;
            overflow: hidden;
        }
        .install-header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: #fff;
            padding: 2rem;
            text-align: center;
        }
        .install-header h1 {
            font-family: 'DM Serif Display', Georgia, serif;
            font-size: 1.75rem;
            margin-bottom: 0.25rem;
        }
        .install-body { padding: 2rem; }
        .step-indicator {
            display: flex;
            justify-content: center;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        .step {
            width: 32px; height: 32px;
            border-radius: 50%;
            background: #e2e8f0;
            display: flex; align-items: center; justify-content: center;
            font-weight: 600; font-size: 0.875rem;
            color: #64748b;
        }
        .step.active {
            background: var(--secondary);
            color: #fff;
        }
        .step.done {
            background: var(--primary);
            color: #fff;
        }
        .form-label { font-weight: 600; font-size: 0.875rem; color: #1e293b; }
        .form-control:focus {
            border-color: var(--secondary);
            box-shadow: 0 0 0 0.2rem rgba(13,148,136,0.2);
        }
        .btn-install {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: #fff;
            border: none;
            font-weight: 600;
            padding: 0.75rem 2rem;
            border-radius: 8px;
            width: 100%;
        }
        .btn-install:hover { opacity: 0.9; color: #fff; }
        .success-icon { font-size: 4rem; color: #10b981; }
        .section-title { font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.1em; color: var(--secondary); margin-bottom: 0.75rem; }
    </style>
</head>
<body>
<div class="install-card">
    <div class="install-header">
        <div class="mb-2">
            <svg width="48" height="48" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                <rect width="48" height="48" rx="12" fill="rgba(255,255,255,0.15)"/>
                <path d="M12 36V16l12-8 12 8v20H28V26h-8v10H12z" fill="rgba(255,255,255,0.9)"/>
                <circle cx="24" cy="20" r="3" fill="rgba(13,148,136,0.8)"/>
            </svg>
        </div>
        <h1>OJS Developer Indonesia</h1>
        <p class="mb-0 opacity-75">Instalasi Website</p>
    </div>
    <div class="install-body">
        <?php if ($success): ?>
        <!-- ── SUCCESS ── -->
        <div class="text-center py-3">
            <div class="success-icon mb-3"><i class="fas fa-check-circle"></i></div>
            <h3 class="fw-bold mb-2" style="color:var(--primary)">Instalasi Berhasil!</h3>
            <p class="text-muted mb-4">Website OJS Developer Indonesia berhasil diinstal. Anda dapat mulai menggunakan situs dan masuk ke panel admin.</p>
            <div class="d-grid gap-2">
                <a href="index.php" class="btn btn-install">
                    <i class="fas fa-home me-2"></i>Kunjungi Website
                </a>
                <a href="admin/" class="btn btn-outline-secondary">
                    <i class="fas fa-cog me-2"></i>Masuk Panel Admin
                </a>
            </div>
        </div>
        <?php elseif ($step === 1): ?>
        <!-- ── STEP 1: Requirements Check ── -->
        <div class="step-indicator">
            <div class="step active">1</div>
            <div class="step">2</div>
            <div class="step">3</div>
        </div>
        <h5 class="fw-bold mb-3">Persyaratan Sistem</h5>
        <?php
        $checks = [
            'PHP 7.4+' => version_compare(PHP_VERSION, '7.4.0', '>='),
            'Ekstensi PDO' => extension_loaded('pdo'),
            'Ekstensi PDO MySQL' => extension_loaded('pdo_mysql'),
            'Ekstensi FileInfo' => extension_loaded('fileinfo'),
            'Direktori Dapat Ditulis' => is_writable(__DIR__),
        ];
        $allOk = !in_array(false, $checks);
        ?>
        <ul class="list-group mb-4">
            <?php foreach ($checks as $label => $ok): ?>
            <li class="list-group-item d-flex justify-content-between align-items-center">
                <?= htmlspecialchars($label) ?>
                <?php if ($ok): ?>
                    <span class="badge bg-success"><i class="fas fa-check me-1"></i>OK</span>
                <?php else: ?>
                    <span class="badge bg-danger"><i class="fas fa-times me-1"></i>Gagal</span>
                <?php endif; ?>
            </li>
            <?php endforeach; ?>
            <li class="list-group-item d-flex justify-content-between align-items-center">
                Versi PHP Saat Ini
                <span class="badge bg-secondary"><?= PHP_VERSION ?></span>
            </li>
        </ul>
        <?php if ($allOk): ?>
        <a href="install.php?step=2" class="btn btn-install">
            <i class="fas fa-arrow-right me-2"></i>Lanjutkan Instalasi
        </a>
        <?php else: ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-triangle me-2"></i>
            Beberapa persyaratan tidak terpenuhi. Harap perbaiki sebelum melanjutkan.
        </div>
        <?php endif; ?>

        <?php else: ?>
        <!-- ── STEP 2: Configuration Form ── -->
        <div class="step-indicator">
            <div class="step done">✓</div>
            <div class="step active">2</div>
            <div class="step">3</div>
        </div>
        <h5 class="fw-bold mb-4">Konfigurasi Database & Admin</h5>

        <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <strong><i class="fas fa-exclamation-triangle me-2"></i>Terjadi Kesalahan:</strong>
            <ul class="mb-0 mt-2">
                <?php foreach ($errors as $err): ?>
                <li><?= htmlspecialchars($err) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>

        <form method="POST" action="install.php?step=2">
            <div class="section-title">Konfigurasi Database</div>

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label">Host Database</label>
                    <input type="text" name="db_host" class="form-control"
                        value="<?= htmlspecialchars($_POST['db_host'] ?? 'localhost') ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Nama Database</label>
                    <input type="text" name="db_name" class="form-control"
                        value="<?= htmlspecialchars($_POST['db_name'] ?? 'ojs_developer') ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Username Database</label>
                    <input type="text" name="db_user" class="form-control"
                        value="<?= htmlspecialchars($_POST['db_user'] ?? 'root') ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Password Database</label>
                    <input type="password" name="db_pass" class="form-control"
                        value="<?= htmlspecialchars($_POST['db_pass'] ?? '') ?>">
                    <div class="form-text">Kosongkan jika tidak ada password.</div>
                </div>
            </div>

            <hr class="my-4">
            <div class="section-title">Konfigurasi Situs</div>

            <div class="mb-3">
                <label class="form-label">URL Situs</label>
                <input type="url" name="site_url" class="form-control"
                    value="<?= htmlspecialchars($_POST['site_url'] ?? $guessedUrl) ?>" required>
                <div class="form-text">Contoh: http://localhost/ojs-developer atau https://ojsdeveloper.id</div>
            </div>

            <hr class="my-4">
            <div class="section-title">Akun Administrator</div>

            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label">Nama Lengkap</label>
                    <input type="text" name="admin_name" class="form-control"
                        value="<?= htmlspecialchars($_POST['admin_name'] ?? '') ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Username Admin</label>
                    <input type="text" name="admin_user" class="form-control"
                        value="<?= htmlspecialchars($_POST['admin_user'] ?? 'admin') ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Email Admin</label>
                    <input type="email" name="admin_email" class="form-control"
                        value="<?= htmlspecialchars($_POST['admin_email'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Password Admin</label>
                    <input type="password" name="admin_pass" class="form-control"
                        placeholder="Min. 8 karakter" required minlength="8">
                </div>
            </div>

            <button type="submit" class="btn btn-install">
                <i class="fas fa-database me-2"></i>Instal Sekarang
            </button>
        </form>
        <?php endif; ?>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
