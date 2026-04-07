<?php
/**
 * Admin Login Page
 */

// If already logged in, redirect to dashboard
if (isLoggedIn()) {
    redirect('index.php');
}

$error = '';
$site_name = function_exists('getSetting') ? getSetting('site_name', 'OJS Developer Indonesia') : 'OJS Developer Indonesia';

// ── Brute-force protection ──
$maxAttempts    = 5;
$lockoutMinutes = 15;
if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = 0;
    $_SESSION['login_lockout']  = 0;
}
$isLocked = ($_SESSION['login_lockout'] > time());

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($isLocked) {
        $remaining = (int)ceil(($_SESSION['login_lockout'] - time()) / 60);
        $error = "Terlalu banyak percobaan login. Coba lagi dalam {$remaining} menit.";
    } elseif (!verify_csrf($_POST['csrf_token'] ?? '')) {
        $error = 'Token keamanan tidak valid. Silakan coba lagi.';
    } else {
        $username = sanitize($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($username) || empty($password)) {
            $error = 'Username dan password wajib diisi.';
        } else {
            // Fetch admin from DB
            $admin = fetch("SELECT * FROM admins WHERE username = ?", [$username]);

            if ($admin && password_verify($password, $admin['password'])) {
                // Login success — reset counters
                $_SESSION['login_attempts'] = 0;
                $_SESSION['login_lockout']  = 0;
                session_regenerate_id(true);
                $_SESSION['admin_id']   = $admin['id'];
                $_SESSION['admin_name'] = $admin['name'];
                $_SESSION['admin_user'] = $admin['username'];
                $_SESSION['logged_in']  = true;

                flash('success', 'Selamat datang kembali, ' . $admin['name'] . '!');
                redirect('index.php');
            } else {
                $_SESSION['login_attempts']++;
                if ($_SESSION['login_attempts'] >= $maxAttempts) {
                    $_SESSION['login_lockout'] = time() + ($lockoutMinutes * 60);
                    $error = "Terlalu banyak percobaan login. Coba lagi dalam {$lockoutMinutes} menit.";
                } else {
                    $left = $maxAttempts - $_SESSION['login_attempts'];
                    $error = "Username atau password salah. Sisa percobaan: {$left}.";
                }
            }
        }
    }
}

$csrf = csrf_token();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin &mdash; <?= htmlspecialchars($site_name) ?></title>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Bootstrap 5.3.3 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome 6.5 -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

    <style>
        :root {
            --primary: #1a365d;
            --secondary: #0d9488;
            --accent: #d97706;
        }

        * { box-sizing: border-box; }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: #f1f5f9;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
            overflow: hidden;
        }

        /* Animated background */
        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background:
                radial-gradient(circle at 20% 20%, rgba(13,148,136,0.12) 0%, transparent 50%),
                radial-gradient(circle at 80% 80%, rgba(26,54,93,0.12) 0%, transparent 50%),
                radial-gradient(circle at 50% 50%, rgba(217,119,6,0.05) 0%, transparent 70%);
            z-index: 0;
        }

        .login-wrapper {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 440px;
        }

        /* Logo/Brand */
        .login-brand {
            text-align: center;
            margin-bottom: 32px;
        }

        .login-brand-icon {
            width: 64px;
            height: 64px;
            background: linear-gradient(135deg, var(--secondary), #0f766e);
            border-radius: 16px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            color: #fff;
            margin-bottom: 14px;
            box-shadow: 0 8px 24px rgba(13,148,136,0.35);
        }

        .login-brand h1 {
            font-size: 20px;
            font-weight: 800;
            color: var(--primary);
            margin: 0 0 4px;
        }

        .login-brand p {
            font-size: 13px;
            color: #64748b;
            margin: 0;
        }

        /* Card */
        .login-card {
            background: #fff;
            border-radius: 20px;
            padding: 36px 40px;
            box-shadow: 0 4px 24px rgba(0,0,0,0.08), 0 20px 60px rgba(0,0,0,0.06);
            border: 1px solid #e2e8f0;
        }

        .login-card h2 {
            font-size: 22px;
            font-weight: 800;
            color: #1e293b;
            margin: 0 0 6px;
        }

        .login-card .subtitle {
            font-size: 13px;
            color: #64748b;
            margin-bottom: 28px;
        }

        .form-label {
            font-weight: 600;
            font-size: 13px;
            color: #374151;
            margin-bottom: 6px;
        }

        .form-control {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 14px;
            border: 1.5px solid #e2e8f0;
            border-radius: 8px;
            padding: 11px 14px;
            transition: all 0.2s ease;
        }

        .form-control:focus {
            border-color: var(--secondary);
            box-shadow: 0 0 0 3px rgba(13,148,136,0.12);
            outline: none;
        }

        .input-group .form-control {
            border-right: none;
        }

        .input-group .input-group-text {
            background: #f8fafc;
            border: 1.5px solid #e2e8f0;
            border-left: none;
            border-radius: 0 8px 8px 0;
            cursor: pointer;
            color: #94a3b8;
            transition: color 0.2s;
        }

        .input-group .input-group-text:hover {
            color: var(--primary);
        }

        .btn-login {
            background: linear-gradient(135deg, var(--primary), #1e4a7a);
            border: none;
            border-radius: 8px;
            padding: 12px;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 14.5px;
            font-weight: 700;
            color: #fff;
            width: 100%;
            cursor: pointer;
            transition: all 0.25s ease;
            box-shadow: 0 4px 14px rgba(26,54,93,0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-login:hover {
            background: linear-gradient(135deg, #162d4f, #1a365d);
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(26,54,93,0.4);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .alert-error {
            background: #fee2e2;
            border: 1px solid #fecaca;
            border-radius: 8px;
            padding: 12px 16px;
            color: #991b1b;
            font-size: 13.5px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 20px;
        }

        .login-footer {
            text-align: center;
            margin-top: 24px;
            font-size: 12px;
            color: #94a3b8;
        }

        .login-footer a {
            color: var(--secondary);
            text-decoration: none;
            font-weight: 600;
        }

        .login-footer a:hover {
            text-decoration: underline;
        }

        /* Security badge */
        .security-badge {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            font-size: 11px;
            color: #94a3b8;
            margin-top: 20px;
        }

        .security-badge i {
            color: var(--secondary);
            font-size: 12px;
        }

        @media (max-width: 480px) {
            .login-card {
                padding: 28px 24px;
            }
        }
    </style>
</head>
<body>

<div class="login-wrapper">
    <!-- Brand -->
    <div class="login-brand">
        <div class="login-brand-icon">
            <i class="fas fa-journal-whills"></i>
        </div>
        <h1><?= htmlspecialchars($site_name) ?></h1>
        <p>Panel Administrasi &mdash; Khusus Staff</p>
    </div>

    <!-- Login Card -->
    <div class="login-card">
        <h2>Selamat Datang</h2>
        <p class="subtitle">Masuk ke panel administrasi untuk mengelola website.</p>

        <?php if ($error): ?>
        <div class="alert-error">
            <i class="fas fa-exclamation-circle"></i>
            <?= htmlspecialchars($error) ?>
        </div>
        <?php endif; ?>

        <form method="POST" action="" id="loginForm">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">

            <div class="mb-4">
                <label for="username" class="form-label">
                    <i class="fas fa-user me-1" style="color:#0d9488;"></i>
                    Username
                </label>
                <input
                    type="text"
                    id="username"
                    name="username"
                    class="form-control"
                    placeholder="Masukkan username admin"
                    value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                    required
                    autocomplete="username"
                    autofocus
                >
            </div>

            <div class="mb-4">
                <label for="password" class="form-label">
                    <i class="fas fa-lock me-1" style="color:#0d9488;"></i>
                    Password
                </label>
                <div class="input-group">
                    <input
                        type="password"
                        id="password"
                        name="password"
                        class="form-control"
                        placeholder="Masukkan password"
                        required
                        autocomplete="current-password"
                    >
                    <span class="input-group-text" onclick="togglePassword()">
                        <i class="fas fa-eye" id="pwIcon"></i>
                    </span>
                </div>
            </div>

            <button type="submit" class="btn-login">
                <i class="fas fa-sign-in-alt"></i>
                Masuk ke Panel Admin
            </button>
        </form>

        <div class="security-badge">
            <i class="fas fa-shield-alt"></i>
            <span>Koneksi aman &bull; Sesi terenkripsi</span>
        </div>
    </div>

    <div class="login-footer">
        <a href="../index.php">
            <i class="fas fa-arrow-left me-1"></i>Kembali ke Website
        </a>
    </div>
</div>

<script>
function togglePassword() {
    const input = document.getElementById('password');
    const icon = document.getElementById('pwIcon');
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.replace('fa-eye', 'fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.replace('fa-eye-slash', 'fa-eye');
    }
}

// Add loading state on submit
document.getElementById('loginForm').addEventListener('submit', function() {
    const btn = this.querySelector('.btn-login');
    btn.innerHTML = '<i class="fas fa-circle-notch fa-spin"></i> Memverifikasi...';
    btn.disabled = true;
});
</script>

</body>
</html>
