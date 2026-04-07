<?php
/**
 * Admin Pesanan Form (Buat / Edit Pesanan)
 */

$id      = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$is_edit = $id > 0;
$errors  = [];

$order = [
    'client_name'        => '',
    'client_email'       => '',
    'client_phone'       => '',
    'client_institution' => '',
    'service_type'       => 'setup_ojs',
    'package_tier'       => 'basic',
    'description'        => '',
    'notes'              => '',
    'status'             => 'pending',
    'price'              => '',
];

// Load existing order for edit
if ($is_edit) {
    try {
        $existing = fetch("SELECT * FROM orders WHERE id = ?", [$id]);
        if (!$existing) {
            flash('error', 'Pesanan tidak ditemukan.');
            redirect('index.php?page=pesanan');
        }
        $order = array_merge($order, $existing);
    } catch (Exception $e) {
        flash('error', 'Gagal memuat data pesanan.');
        redirect('index.php?page=pesanan');
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        flash('error', 'Token keamanan tidak valid.');
        redirect('index.php?page=pesanan' . ($is_edit ? '-form&id=' . $id : ''));
    }

    // Collect input
    $order['client_name']        = sanitize($_POST['client_name'] ?? '');
    $order['client_email']       = sanitize($_POST['client_email'] ?? '');
    $order['client_phone']       = sanitize($_POST['client_phone'] ?? '');
    $order['client_institution'] = sanitize($_POST['client_institution'] ?? '');
    $order['service_type']       = sanitize($_POST['service_type'] ?? 'setup_ojs');
    $order['package_tier']       = sanitize($_POST['package_tier'] ?? 'basic');
    $order['description']        = trim($_POST['description'] ?? '');
    $order['notes']              = trim($_POST['notes'] ?? '');
    $order['status']             = sanitize($_POST['status'] ?? 'pending');
    $order['price']              = (int) preg_replace('/[^0-9]/', '', $_POST['price'] ?? '0');

    // Validate
    $valid_services = ['setup_ojs','migrasi','kustomisasi','pelatihan','maintenance','indeksasi_doaj','indeksasi_sinta','lainnya'];
    $valid_packages = ['basic','professional','premium','custom'];
    $valid_statuses = ['pending','in_progress','completed','cancelled'];

    if (empty($order['client_name'])) {
        $errors[] = 'Nama klien wajib diisi.';
    }
    if (!empty($order['client_email']) && !filter_var($order['client_email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Format email tidak valid.';
    }
    if (!in_array($order['service_type'], $valid_services)) {
        $errors[] = 'Jenis layanan tidak valid.';
    }
    if (!in_array($order['package_tier'], $valid_packages)) {
        $errors[] = 'Paket tidak valid.';
    }
    if (!in_array($order['status'], $valid_statuses)) {
        $errors[] = 'Status tidak valid.';
    }

    if (empty($errors)) {
        try {
            if ($is_edit) {
                // Update existing order
                update('orders', [
                    'client_name'        => $order['client_name'],
                    'client_email'       => $order['client_email'],
                    'client_phone'       => $order['client_phone'],
                    'client_institution' => $order['client_institution'],
                    'service_type'       => $order['service_type'],
                    'package_tier'       => $order['package_tier'],
                    'description'        => $order['description'],
                    'notes'              => $order['notes'],
                    'status'             => $order['status'],
                    'price'              => $order['price'],
                    'updated_at'         => date('Y-m-d H:i:s'),
                ], 'id = ?', [$id]);

                flash('success', 'Pesanan berhasil diperbarui.');
                redirect('index.php?page=pesanan-detail&id=' . $id);
            } else {
                // Create new order
                $tracking_code = generateTrackingCode();

                $new_id = insert('orders', [
                    'tracking_code'      => $tracking_code,
                    'client_name'        => $order['client_name'],
                    'client_email'       => $order['client_email'],
                    'client_phone'       => $order['client_phone'],
                    'client_institution' => $order['client_institution'],
                    'service_type'       => $order['service_type'],
                    'package_tier'       => $order['package_tier'],
                    'description'        => $order['description'],
                    'notes'              => $order['notes'],
                    'status'             => $order['status'],
                    'price'              => $order['price'],
                    'created_at'         => date('Y-m-d H:i:s'),
                    'updated_at'         => date('Y-m-d H:i:s'),
                ]);

                // Create default milestones
                $milestones = getDefaultMilestones($order['service_type']);
                foreach ($milestones as $sort => $m) {
                    insert('order_milestones', [
                        'order_id'    => $new_id,
                        'title'       => $m[0],
                        'description' => $m[1],
                        'status'      => 'pending',
                        'sort_order'  => $sort + 1,
                        'created_at'  => date('Y-m-d H:i:s'),
                    ]);
                }

                // Send email notification to customer
                  if (!empty($order['client_email'])) {
                      $site_name = getSetting('site_name', 'OJS Developer Indonesia');
                      $email_contact = getSetting('email_contact', 'noreply@ojsdeveloper.id');
                      $service_label = $service_options[$order['service_type']] ?? $order['service_type'];
                      $package_label = $package_options[$order['package_tier']] ?? $order['package_tier'];
                      $to      = $order['client_email'];
                      $subject = "[{$site_name}] Pesanan Anda Berhasil Dibuat – Kode: {$tracking_code}";
                      $body    = "Yth. {$order['client_name']},\n\n";
                      $body   .= "Terima kasih telah mempercayakan kebutuhan OJS Anda kepada kami.\n";
                      $body   .= "Pesanan Anda telah berhasil dibuat dengan detail berikut:\n\n";
                      $body   .= "Kode Tracking : {$tracking_code}\n";
                      $body   .= "Layanan       : {$service_label}\n";
                      $body   .= "Paket         : {$package_label}\n";
                      $body   .= "Institusi     : {$order['client_institution']}\n";
                      $body   .= "Status        : Menunggu\n\n";
                      $body   .= "Pantau perkembangan pesanan di:\n";
                      $body   .= getSetting('site_url', '') . "/tracking\n\n";
                      $body   .= "Tim kami segera menghubungi Anda.\n";
                      $body   .= "Salam,\n{$site_name}";
                      $headers  = "From: {$site_name} <{$email_contact}>\r\n";
                      $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
                      @mail($to, $subject, $body, $headers);
                  }
                  flash('success', 'Pesanan berhasil dibuat! Kode tracking: <strong>' . htmlspecialchars($tracking_code) . '</strong>');
                  redirect('index.php?page=pesanan-detail&id=' . $new_id);
            }
        } catch (Exception $e) {
            $errors[] = 'Gagal menyimpan pesanan: ' . $e->getMessage();
        }
    }
}

$csrf = csrf_token();

$service_options = [
    'setup_ojs'       => 'Setup & Instalasi OJS',
    'migrasi'         => 'Migrasi Jurnal',
    'kustomisasi'     => 'Kustomisasi Tampilan',
    'pelatihan'       => 'Pelatihan OJS',
    'maintenance'     => 'Maintenance & Support',
    'indeksasi_doaj'  => 'Indeksasi DOAJ',
    'indeksasi_sinta' => 'Indeksasi SINTA',
    'lainnya'         => 'Lainnya',
];

$package_options = [
    'basic'        => 'Basic',
    'professional' => 'Professional',
    'premium'      => 'Premium',
    'custom'       => 'Custom',
];

$status_options = [
    'pending'     => 'Menunggu',
    'in_progress' => 'Sedang Dikerjakan',
    'completed'   => 'Selesai',
    'cancelled'   => 'Dibatalkan',
];

require_once ADMIN_PATH . '/includes/header.php';
require_once ADMIN_PATH . '/includes/sidebar.php';
?>

<div class="admin-content">
    <!-- Page Header -->
    <div class="page-header">
        <div class="page-header-left">
            <h2><?= $is_edit ? 'Edit Pesanan' : 'Buat Pesanan Baru' ?></h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="index.php?page=pesanan">Pesanan</a></li>
                    <li class="breadcrumb-item active"><?= $is_edit ? 'Edit' : 'Buat Baru' ?></li>
                </ol>
            </nav>
        </div>
        <div class="page-header-actions">
            <a href="<?= $is_edit ? 'index.php?page=pesanan-detail&id=' . $id : 'index.php?page=pesanan' ?>"
               class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i>Kembali
            </a>
        </div>
    </div>

    <!-- Error Messages -->
    <?php if (!empty($errors)): ?>
    <div class="alert alert-danger mb-4" style="border-radius:10px;border-left:4px solid #dc2626;">
        <div style="font-weight:700;margin-bottom:6px;"><i class="fas fa-exclamation-triangle me-2"></i>Terdapat Kesalahan</div>
        <ul class="mb-0 ps-3">
            <?php foreach ($errors as $err): ?>
            <li><?= htmlspecialchars($err) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>

    <form method="POST" data-loading>
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">

        <div class="row g-4">
            <!-- Left Column: Main Form -->
            <div class="col-xl-8">
                <!-- Informasi Klien -->
                <div class="admin-card mb-4">
                    <div class="admin-card-header">
                        <h5 class="admin-card-title">
                            <i class="fas fa-user"></i>
                            Informasi Klien
                        </h5>
                    </div>
                    <div class="admin-card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">
                                    Nama Klien <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="client_name" class="form-control"
                                       placeholder="Nama lengkap klien"
                                       value="<?= htmlspecialchars($order['client_name']) ?>"
                                       required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Alamat Email</label>
                                <input type="email" name="client_email" class="form-control"
                                       placeholder="email@contoh.com"
                                       value="<?= htmlspecialchars($order['client_email']) ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">No. Telepon / WhatsApp</label>
                                <input type="text" name="client_phone" class="form-control"
                                       placeholder="08xx-xxxx-xxxx"
                                       value="<?= htmlspecialchars($order['client_phone']) ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Institusi / Lembaga</label>
                                <input type="text" name="client_institution" class="form-control"
                                       placeholder="Nama universitas / institusi"
                                       value="<?= htmlspecialchars($order['client_institution']) ?>">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Detail Layanan -->
                <div class="admin-card mb-4">
                    <div class="admin-card-header">
                        <h5 class="admin-card-title">
                            <i class="fas fa-cogs"></i>
                            Detail Layanan
                        </h5>
                    </div>
                    <div class="admin-card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">
                                    Jenis Layanan <span class="text-danger">*</span>
                                </label>
                                <select name="service_type" class="form-select">
                                    <?php foreach ($service_options as $val => $label): ?>
                                    <option value="<?= $val ?>" <?= $order['service_type'] === $val ? 'selected' : '' ?>>
                                        <?= $label ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">
                                    Paket <span class="text-danger">*</span>
                                </label>
                                <select name="package_tier" class="form-select">
                                    <?php foreach ($package_options as $val => $label): ?>
                                    <option value="<?= $val ?>" <?= $order['package_tier'] === $val ? 'selected' : '' ?>>
                                        <?= $label ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Harga / Nilai Pesanan (Rp)</label>
                                <input type="text" name="price" class="form-control"
                                       placeholder="0"
                                       value="<?= number_format((int)($order['price'] ?? 0), 0, ',', '.') ?>"
                                       oninput="this.value=this.value.replace(/[^0-9]/g,'')"
                                       inputmode="numeric">
                                <div class="form-text">Isi untuk mencatat pendapatan pesanan ini.</div>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Deskripsi Kebutuhan</label>
                                <textarea name="description" class="form-control" rows="5"
                                          placeholder="Jelaskan kebutuhan klien secara detail: kondisi saat ini, target yang ingin dicapai, kendala yang dihadapi, dll."
                                          ><?= htmlspecialchars($order['description']) ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Catatan Internal Admin -->
                <div class="admin-card">
                    <div class="admin-card-header">
                        <h5 class="admin-card-title">
                            <i class="fas fa-sticky-note"></i>
                            Catatan Internal Admin
                        </h5>
                    </div>
                    <div class="admin-card-body">
                        <div class="mb-1">
                            <label class="form-label">Catatan (tidak ditampilkan ke klien)</label>
                            <textarea name="notes" class="form-control" rows="4"
                                      placeholder="Catatan internal: hal teknis, kesepakatan harga, informasi penting, dll."
                                      ><?= htmlspecialchars($order['notes']) ?></textarea>
                        </div>
                        <div style="font-size:12px;color:#94a3b8;margin-top:4px;">
                            <i class="fas fa-lock me-1"></i>Catatan ini hanya terlihat oleh admin.
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Status & Info -->
            <div class="col-xl-4">
                <!-- Status & Publish -->
                <div class="admin-card mb-4">
                    <div class="admin-card-header">
                        <h5 class="admin-card-title">
                            <i class="fas fa-tasks"></i>
                            Status Pesanan
                        </h5>
                    </div>
                    <div class="admin-card-body">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select mb-3">
                            <?php foreach ($status_options as $val => $label): ?>
                            <option value="<?= $val ?>" <?= $order['status'] === $val ? 'selected' : '' ?>>
                                <?= $label ?>
                            </option>
                            <?php endforeach; ?>
                        </select>

                        <?php if ($is_edit && !empty($order['tracking_code'])): ?>
                        <div style="background:#f8fafc;border-radius:8px;padding:12px;border:1px solid #e2e8f0;">
                            <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.08em;color:#94a3b8;margin-bottom:6px;">
                                Kode Tracking
                            </div>
                            <code style="font-size:13px;font-weight:700;color:#1e293b;">
                                <?= htmlspecialchars($order['tracking_code']) ?>
                            </code>
                        </div>
                        <?php else: ?>
                        <div style="background:#f0fdf4;border-radius:8px;padding:12px;border:1px solid #bbf7d0;">
                            <div style="font-size:12px;color:#15803d;">
                                <i class="fas fa-magic me-1"></i>
                                Kode tracking akan dibuat otomatis setelah pesanan disimpan.
                            </div>
                            <div style="font-size:11px;color:#94a3b8;margin-top:4px;">
                                Format: <code>SNADA-ddMMyyyy-NNN</code>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Info Card -->
                <?php if (!$is_edit): ?>
                <div class="admin-card mb-4" style="background:linear-gradient(135deg,rgba(13,148,136,0.04),rgba(26,54,93,0.04));">
                    <div class="admin-card-body">
                        <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.08em;color:#94a3b8;margin-bottom:12px;">
                            <i class="fas fa-info-circle me-1"></i>Informasi
                        </div>
                        <div style="font-size:13px;color:#475569;line-height:1.6;">
                            <p class="mb-2">Saat pesanan baru dibuat, sistem akan otomatis:</p>
                            <ul class="mb-0 ps-3" style="font-size:12.5px;">
                                <li>Membuat kode tracking unik</li>
                                <li>Membuat milestone default (10 tahap)</li>
                                <li>Menetapkan status awal: <strong>Menunggu</strong></li>
                            </ul>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Action Buttons -->
                <div class="admin-card">
                    <div class="admin-card-body">
                        <button type="submit" class="btn btn-primary w-100 mb-2">
                            <i class="fas fa-save me-2"></i>
                            <?= $is_edit ? 'Simpan Perubahan' : 'Buat Pesanan' ?>
                        </button>
                        <a href="<?= $is_edit ? 'index.php?page=pesanan-detail&id=' . $id : 'index.php?page=pesanan' ?>"
                           class="btn btn-outline-secondary w-100">
                            <i class="fas fa-times me-2"></i>Batal
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<?php require_once ADMIN_PATH . '/includes/footer.php'; ?>
