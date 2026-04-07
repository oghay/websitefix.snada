<?php
/**
 * Admin Pesanan Detail + Manajemen Milestone
 */

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$id) {
    flash('error', 'ID pesanan tidak valid.');
    redirect('index.php?page=pesanan');
}

// Load order
$order = null;
try {
    $order = fetch("SELECT * FROM orders WHERE id = ?", [$id]);
} catch (Exception $e) {}

if (!$order) {
    flash('error', 'Pesanan tidak ditemukan.');
    redirect('index.php?page=pesanan');
}

// ============================================================
// AJAX handlers (milestone status update, add, delete)
// ============================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if AJAX request
    $is_ajax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        if ($is_ajax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Token keamanan tidak valid.']);
            exit;
        }
        flash('error', 'Token keamanan tidak valid.');
        redirect('index.php?page=pesanan-detail&id=' . $id);
    }

    $action = sanitize($_POST['action'] ?? '');

    // ---- Update Milestone Status ----
    if ($action === 'update_milestone_status') {
        $milestone_id = (int)($_POST['milestone_id'] ?? 0);
        $new_status   = sanitize($_POST['status'] ?? '');
        $valid_ms     = ['pending', 'in_progress', 'completed'];

        if (!$milestone_id || !in_array($new_status, $valid_ms)) {
            if ($is_ajax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Data tidak valid.']);
                exit;
            }
            redirect('index.php?page=pesanan-detail&id=' . $id);
        }

        try {
            $ms_data = ['status' => $new_status];
            if ($new_status === 'completed') {
                $ms_data['completed_at'] = date('Y-m-d H:i:s');
            } elseif ($new_status === 'in_progress' || $new_status === 'pending') {
                $ms_data['completed_at'] = null;
            }
            update('order_milestones', $ms_data, 'id = ? AND order_id = ?', [$milestone_id, $id]);

              // Auto-complete all previous milestones when one is set to in_progress or completed
              if ($new_status === 'in_progress' || $new_status === 'completed') {
                  // Get sort_order of the current milestone
                  $cur_ms = fetch("SELECT sort_order FROM order_milestones WHERE id = ? AND order_id = ?", [$milestone_id, $id]);
                  if ($cur_ms) {
                      $cur_sort = (int)$cur_ms['sort_order'];
                      $now_ms   = date('Y-m-d H:i:s');
                      db()->prepare(
                          "UPDATE order_milestones SET status = 'completed', completed_at = ? WHERE order_id = ? AND sort_order < ? AND status != 'completed'"
                      )->execute([$now_ms, $id, $cur_sort]);
                  }
              }

            // Auto-update order status
            // If any milestone is in_progress → order in_progress
            if ($new_status === 'in_progress') {
                update('orders', ['status' => 'in_progress', 'updated_at' => date('Y-m-d H:i:s')], 'id = ? AND status = ?', [$id, 'pending']);
            }

            // If all milestones are completed → order completed
            $remaining = fetch(
                "SELECT COUNT(*) as cnt FROM order_milestones WHERE order_id = ? AND status != 'completed'",
                [$id]
            );
            if ($remaining && (int)$remaining['cnt'] === 0) {
                update('orders', ['status' => 'completed', 'updated_at' => date('Y-m-d H:i:s')], 'id = ?', [$id]);
                $order_status_updated = 'completed';
            } else {
                $order_status_updated = null;
            }

            // ── WhatsApp Notification ──
            $wa_result = ['success' => false, 'message' => 'skip'];
            if ($new_status !== 'pending' && !empty($order['client_phone'])) {
                // Get milestone title
                $ms_row = fetch("SELECT title FROM order_milestones WHERE id = ?", [$milestone_id]);
                $ms_title = $ms_row ? $ms_row['title'] : 'Milestone';
                $wa_msg = buildMilestoneNotifMessage($order, $ms_title, $new_status);
                $wa_result = sendWhatsAppNotification($order['client_phone'], $wa_msg);
            }

            if ($is_ajax) {
                header('Content-Type: application/json');
                // Reload fresh order
                $fresh_order = fetch("SELECT status FROM orders WHERE id = ?", [$id]);
                echo json_encode([
                    'success'      => true,
                    'message'      => 'Status milestone berhasil diperbarui.',
                    'order_status' => $fresh_order ? $fresh_order['status'] : $order['status'],
                    'wa_sent'      => $wa_result['success'],
                    'wa_message'   => $wa_result['message'],
                ]);
                exit;
            }

            $flash_msg = 'Status milestone berhasil diperbarui.';
            if ($wa_result['success']) {
                $flash_msg .= ' Notifikasi WA terkirim.';
            }
            flash('success', $flash_msg);
        } catch (Exception $e) {
            if ($is_ajax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Gagal: ' . $e->getMessage()]);
                exit;
            }
            flash('error', 'Gagal memperbarui milestone.');
        }
        redirect('index.php?page=pesanan-detail&id=' . $id);
    }

    // ---- Add Custom Milestone ----
    if ($action === 'add_milestone') {
        $title       = sanitize($_POST['milestone_title'] ?? '');
        $description = trim($_POST['milestone_description'] ?? '');

        if (empty($title)) {
            if ($is_ajax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Judul milestone wajib diisi.']);
                exit;
            }
            flash('error', 'Judul milestone wajib diisi.');
            redirect('index.php?page=pesanan-detail&id=' . $id);
        }

        try {
            // Get max sort_order
            $max_sort = fetch("SELECT MAX(sort_order) as mx FROM order_milestones WHERE order_id = ?", [$id]);
            $next_sort = ($max_sort && $max_sort['mx'] !== null) ? (int)$max_sort['mx'] + 1 : 1;

            $new_ms_id = insert('order_milestones', [
                'order_id'    => $id,
                'title'       => $title,
                'description' => $description,
                'status'      => 'pending',
                'sort_order'  => $next_sort,
                'created_at'  => date('Y-m-d H:i:s'),
            ]);

            if ($is_ajax) {
                header('Content-Type: application/json');
                echo json_encode([
                    'success'    => true,
                    'message'    => 'Milestone berhasil ditambahkan.',
                    'milestone'  => [
                        'id'          => (int)$new_ms_id,
                        'title'       => $title,
                        'description' => $description,
                        'status'      => 'pending',
                        'sort_order'  => $next_sort,
                    ],
                ]);
                exit;
            }
            flash('success', 'Milestone berhasil ditambahkan.');
        } catch (Exception $e) {
            if ($is_ajax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Gagal: ' . $e->getMessage()]);
                exit;
            }
            flash('error', 'Gagal menambahkan milestone.');
        }
        redirect('index.php?page=pesanan-detail&id=' . $id);
    }

    // ---- Delete Milestone ----
    if ($action === 'delete_milestone') {
        $milestone_id = (int)($_POST['milestone_id'] ?? 0);

        if (!$milestone_id) {
            if ($is_ajax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'ID milestone tidak valid.']);
                exit;
            }
            redirect('index.php?page=pesanan-detail&id=' . $id);
        }

        try {
            delete('order_milestones', 'id = ? AND order_id = ?', [$milestone_id, $id]);

            if ($is_ajax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'message' => 'Milestone berhasil dihapus.']);
                exit;
            }
            flash('success', 'Milestone berhasil dihapus.');
        } catch (Exception $e) {
            if ($is_ajax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Gagal: ' . $e->getMessage()]);
                exit;
            }
            flash('error', 'Gagal menghapus milestone.');
        }
        redirect('index.php?page=pesanan-detail&id=' . $id);
    }

    // ---- Update Order Status (right panel) ----
    if ($action === 'update_order_status') {
        $new_status     = sanitize($_POST['status'] ?? '');
        $valid_statuses = ['pending', 'in_progress', 'completed', 'cancelled'];
        if (in_array($new_status, $valid_statuses)) {
            try {
                update('orders', ['status' => $new_status, 'updated_at' => date('Y-m-d H:i:s')], 'id = ?', [$id]);

                // Auto-update milestones when order is completed
                if ($new_status === 'completed') {
                    $now = date('Y-m-d H:i:s');
                    $pdo = db();
                    $pdo->prepare("UPDATE order_milestones SET status = 'completed', completed_at = ? WHERE order_id = ? AND status != 'completed'")->execute([$now, $id]);
                }
                // Auto-reset milestones to pending when order is cancelled
                if ($new_status === 'cancelled') {
                    db()->prepare("UPDATE order_milestones SET status = 'pending', completed_at = NULL WHERE order_id = ?")->execute([$id]);
                }

                flash('success', 'Status pesanan berhasil diperbarui.');
            } catch (Exception $e) {
                flash('error', 'Gagal memperbarui status.');
            }
        }
        redirect('index.php?page=pesanan-detail&id=' . $id);
    }
}

// Reload fresh data
try {
    $order = fetch("SELECT * FROM orders WHERE id = ?", [$id]);
} catch (Exception $e) {}

// Load milestones
$milestones = [];
try {
    $milestones = fetchAll(
        "SELECT * FROM order_milestones WHERE order_id = ? ORDER BY sort_order ASC, id ASC",
        [$id]
    );
} catch (Exception $e) {}

$csrf = csrf_token();

$service_labels = [
    'setup_ojs'       => 'Setup & Instalasi OJS',
    'migrasi'         => 'Migrasi Jurnal',
    'kustomisasi'     => 'Kustomisasi Tampilan',
    'pelatihan'       => 'Pelatihan OJS',
    'maintenance'     => 'Maintenance & Support',
    'indeksasi_doaj'  => 'Indeksasi DOAJ',
    'indeksasi_sinta' => 'Indeksasi SINTA',
    'lainnya'         => 'Lainnya',
];

$package_labels = [
    'basic'        => 'Basic',
    'professional' => 'Professional',
    'premium'      => 'Premium',
    'custom'       => 'Custom',
];

// Count milestones by status
$ms_completed  = count(array_filter($milestones, fn($m) => $m['status'] === 'completed'));
$ms_total      = count($milestones);
$ms_percentage = $ms_total > 0 ? round($ms_completed / $ms_total * 100) : 0;

require_once ADMIN_PATH . '/includes/header.php';
require_once ADMIN_PATH . '/includes/sidebar.php';
?>

<div class="admin-content">
    <!-- Page Header -->
    <div class="page-header">
        <div class="page-header-left">
            <h2>Detail Pesanan</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="index.php?page=pesanan">Pesanan</a></li>
                    <li class="breadcrumb-item active"><?= htmlspecialchars($order['tracking_code']) ?></li>
                </ol>
            </nav>
        </div>
        <div class="page-header-actions">
            <a href="index.php?page=pesanan" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i>Kembali ke Daftar
            </a>
            <a href="index.php?page=pesanan-form&id=<?= $id ?>" class="btn btn-outline-primary">
                <i class="fas fa-edit me-1"></i>Edit Pesanan
            </a>
        </div>
    </div>

    <div class="row g-4">
        <!-- Left Column: Order Info + Milestones -->
        <div class="col-xl-8">

            <!-- Order Info Card -->
            <div class="admin-card mb-4">
                <div class="admin-card-header">
                    <h5 class="admin-card-title">
                        <i class="fas fa-info-circle"></i>
                        Informasi Pesanan
                    </h5>
                    <span class="badge bg-<?= getOrderStatusBadge($order['status']) ?>"
                          style="font-size:12px;padding:6px 14px;" id="orderStatusBadge">
                        <?= getOrderStatusLabel($order['status']) ?>
                    </span>
                </div>
                <div class="admin-card-body">
                    <!-- Tracking Code -->
                    <div style="background:linear-gradient(135deg,#1a365d,#0d9488);border-radius:12px;padding:20px 24px;margin-bottom:24px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">
                        <div>
                            <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.1em;color:rgba(255,255,255,0.65);margin-bottom:4px;">
                                Kode Tracking
                            </div>
                            <div style="font-size:22px;font-weight:800;color:#fff;letter-spacing:0.04em;font-family:'Courier New',monospace;" id="trackingCodeDisplay">
                                <?= htmlspecialchars($order['tracking_code']) ?>
                            </div>
                        </div>
                        <button type="button"
                                class="btn btn-light btn-sm"
                                onclick="copyTrackingCode()"
                                id="copyTrackingBtn"
                                style="font-size:13px;">
                            <i class="fas fa-copy me-1"></i>Salin Kode
                        </button>
                    </div>

                    <!-- Client Info Grid -->
                    <div class="row g-0">
                        <div class="col-md-6">
                            <div class="detail-item">
                                <span class="detail-label">Nama Klien</span>
                                <span class="detail-value"><?= htmlspecialchars($order['client_name']) ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Alamat Email</span>
                                <span class="detail-value">
                                    <?php if (!empty($order['client_email'])): ?>
                                        <a href="mailto:<?= htmlspecialchars($order['client_email']) ?>" style="color:var(--secondary);">
                                            <?= htmlspecialchars($order['client_email']) ?>
                                        </a>
                                    <?php else: ?>
                                        <span style="color:#94a3b8;">Tidak disediakan</span>
                                    <?php endif; ?>
                                </span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">No. Telepon / WhatsApp</span>
                                <span class="detail-value">
                                    <?php if (!empty($order['client_phone'])): ?>
                                        <?php
                                        $wa = preg_replace('/[^0-9]/', '', $order['client_phone']);
                                        if (substr($wa, 0, 1) === '0') $wa = '62' . substr($wa, 1);
                                        ?>
                                        <a href="https://wa.me/<?= $wa ?>" target="_blank" style="color:var(--secondary);">
                                            <i class="fab fa-whatsapp me-1"></i>
                                            <?= htmlspecialchars($order['client_phone']) ?>
                                        </a>
                                    <?php else: ?>
                                        <span style="color:#94a3b8;">Tidak disediakan</span>
                                    <?php endif; ?>
                                </span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="detail-item">
                                <span class="detail-label">Institusi / Lembaga</span>
                                <span class="detail-value"><?= htmlspecialchars($order['client_institution'] ?: '-') ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Jenis Layanan</span>
                                <span class="detail-value">
                                    <?= htmlspecialchars($service_labels[$order['service_type']] ?? $order['service_type']) ?>
                                </span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Paket</span>
                                <span class="detail-value">
                                    <?= htmlspecialchars($package_labels[$order['package_tier']] ?? $order['package_tier']) ?>
                                </span>
                            </div>
                        </div>
                    </div>

                    <?php if (!empty($order['description'])): ?>
                    <div style="margin-top:16px;">
                        <div style="font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:0.06em;color:#94a3b8;margin-bottom:8px;">Deskripsi Kebutuhan</div>
                        <div style="background:#f8fafc;border-radius:10px;padding:16px;border-left:4px solid var(--secondary);font-size:14px;line-height:1.7;color:#374151;white-space:pre-line;">
                            <?= htmlspecialchars($order['description']) ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($order['notes'])): ?>
                    <div style="margin-top:16px;">
                        <div style="font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:0.06em;color:#94a3b8;margin-bottom:8px;">
                            <i class="fas fa-lock me-1"></i>Catatan Internal Admin
                        </div>
                        <div style="background:#fffbeb;border-radius:10px;padding:16px;border-left:4px solid #f59e0b;font-size:14px;line-height:1.7;color:#374151;white-space:pre-line;">
                            <?= htmlspecialchars($order['notes']) ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Timestamps -->
                    <div class="row g-3 mt-2">
                        <div class="col-sm-6">
                            <div style="text-align:center;padding:14px;background:#f8fafc;border-radius:10px;border:1px solid #e2e8f0;">
                                <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.08em;color:#94a3b8;margin-bottom:5px;">Tanggal Dibuat</div>
                                <div style="font-size:14px;font-weight:700;color:#1e293b;"><?= date('d M Y', strtotime($order['created_at'])) ?></div>
                                <div style="font-size:12px;color:#64748b;"><?= date('H:i', strtotime($order['created_at'])) ?> WIB</div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div style="text-align:center;padding:14px;background:#f8fafc;border-radius:10px;border:1px solid #e2e8f0;">
                                <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.08em;color:#94a3b8;margin-bottom:5px;">Terakhir Diperbarui</div>
                                <div style="font-size:14px;font-weight:700;color:#1e293b;"><?= date('d M Y', strtotime($order['updated_at'])) ?></div>
                                <div style="font-size:12px;color:#64748b;"><?= date('H:i', strtotime($order['updated_at'])) ?> WIB</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Milestone Timeline -->
            <div class="admin-card">
                <div class="admin-card-header">
                    <h5 class="admin-card-title">
                        <i class="fas fa-tasks"></i>
                        Milestone Pengerjaan
                        <span class="badge" style="background:#e2e8f0;color:#475569;font-size:11px;margin-left:4px;">
                            <?= $ms_completed ?>/<?= $ms_total ?>
                        </span>
                    </h5>
                    <!-- Add Milestone Button -->
                    <div class="d-flex gap-2">
                    <button type="button" class="btn btn-sm btn-outline-success" onclick="markAllMilestonesComplete()" title="Tandai semua tahapan selesai">
                        <i class="fas fa-check-double me-1"></i>Semua Selesai
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#addMilestoneModal">
                        <i class="fas fa-plus me-1"></i>Tambah Milestone
                    </button>
                    </div>
                </div>
                <div class="admin-card-body">
                    <!-- Progress Bar -->
                    <?php if ($ms_total > 0): ?>
                    <div style="margin-bottom:24px;">
                        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px;">
                            <span style="font-size:13px;color:#64748b;">Progress Pengerjaan</span>
                            <span style="font-size:13px;font-weight:700;color:#1e293b;"><?= $ms_percentage ?>%</span>
                        </div>
                        <div style="height:10px;background:#e2e8f0;border-radius:10px;overflow:hidden;">
                            <div style="height:100%;width:<?= $ms_percentage ?>%;background:linear-gradient(90deg,#0d9488,#22c55e);border-radius:10px;transition:width 0.5s ease;"></div>
                        </div>
                        <div style="font-size:11px;color:#94a3b8;margin-top:4px;"><?= $ms_completed ?> dari <?= $ms_total ?> milestone selesai</div>
                    </div>
                    <?php endif; ?>

                    <!-- Timeline -->
                    <?php if (!empty($milestones)): ?>
                    <div class="milestone-timeline" id="milestoneTimeline">
                        <?php foreach ($milestones as $step => $ms): ?>
                        <?php
                        // Determine colors
                        $ms_color = '#94a3b8'; // pending = gray
                        $ms_bg    = '#f1f5f9';
                        $ms_border= '#cbd5e1';
                        if ($ms['status'] === 'in_progress') {
                            $ms_color  = '#0e7490';
                            $ms_bg     = '#ecfeff';
                            $ms_border = '#a5f3fc';
                        } elseif ($ms['status'] === 'completed') {
                            $ms_color  = '#15803d';
                            $ms_bg     = '#f0fdf4';
                            $ms_border = '#bbf7d0';
                        }
                        $is_last = ($step === count($milestones) - 1);
                        ?>
                        <div class="milestone-item" id="milestone-<?= $ms['id'] ?>"
                             data-id="<?= $ms['id'] ?>"
                             data-status="<?= $ms['status'] ?>"
                             style="display:flex;gap:16px;margin-bottom:<?= $is_last ? '0' : '0' ?>;position:relative;">

                            <!-- Step Number + Line -->
                            <div style="display:flex;flex-direction:column;align-items:center;flex-shrink:0;">
                                <div style="width:36px;height:36px;border-radius:50%;background:<?= $ms_bg ?>;border:2px solid <?= $ms_border ?>;display:flex;align-items:center;justify-content:center;z-index:1;flex-shrink:0;">
                                    <?php if ($ms['status'] === 'completed'): ?>
                                        <i class="fas fa-check" style="color:<?= $ms_color ?>;font-size:14px;"></i>
                                    <?php elseif ($ms['status'] === 'in_progress'): ?>
                                        <i class="fas fa-spinner fa-spin" style="color:<?= $ms_color ?>;font-size:13px;"></i>
                                    <?php else: ?>
                                        <span style="font-size:12px;font-weight:700;color:<?= $ms_color ?>;"><?= $step + 1 ?></span>
                                    <?php endif; ?>
                                </div>
                                <?php if (!$is_last): ?>
                                <div style="width:2px;flex:1;min-height:24px;background:<?= $ms_border ?>;margin:4px 0;"></div>
                                <?php endif; ?>
                            </div>

                            <!-- Content -->
                            <div style="flex:1;padding-bottom:<?= $is_last ? '0' : '20px' ?>;">
                                <div style="background:<?= $ms_bg ?>;border:1px solid <?= $ms_border ?>;border-radius:10px;padding:14px 16px;">
                                    <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:8px;">
                                        <div>
                                            <div style="font-weight:700;font-size:14px;color:#1e293b;margin-bottom:2px;">
                                                <?= htmlspecialchars($ms['title']) ?>
                                            </div>
                                            <?php if (!empty($ms['description'])): ?>
                                            <div style="font-size:13px;color:#64748b;line-height:1.5;">
                                                <?= htmlspecialchars($ms['description']) ?>
                                            </div>
                                            <?php endif; ?>
                                            <?php if (!empty($ms['completed_at'])): ?>
                                            <div style="font-size:11px;color:#15803d;margin-top:4px;">
                                                <i class="fas fa-check-circle me-1"></i>
                                                Selesai: <?= date('d M Y H:i', strtotime($ms['completed_at'])) ?>
                                            </div>
                                            <?php endif; ?>
                                        </div>
                                        <div style="display:flex;align-items:center;gap:6px;flex-shrink:0;">
                                            <!-- Status Badge -->
                                            <span class="milestone-status-badge" style="font-size:11px;padding:4px 10px;border-radius:20px;font-weight:600;background:<?= $ms_bg ?>;color:<?= $ms_color ?>;border:1px solid <?= $ms_border ?>;">
                                                <?= getMilestoneStatusLabel($ms['status']) ?>
                                            </span>

                                            <!-- Status Change Buttons -->
                                              <?php if ($ms['status'] === 'pending'): ?>
                                              <button type="button"
                                                      class="btn btn-sm btn-info milestone-action-btn"
                                                      style="font-size:12px;"
                                                      onclick="updateMilestoneStatus(<?= $ms['id'] ?>, 'in_progress', this)">
                                                  <i class="fas fa-play me-1"></i>Mulai Kerjakan
                                              </button>
                                              <?php elseif ($ms['status'] === 'in_progress'): ?>
                                              <button type="button"
                                                      class="btn btn-sm btn-success milestone-action-btn"
                                                      style="font-size:12px;"
                                                      onclick="updateMilestoneStatus(<?= $ms['id'] ?>, 'completed', this)">
                                                  <i class="fas fa-check me-1"></i>Tandai Selesai
                                              </button>
                                              <button type="button"
                                                      class="btn btn-sm btn-outline-secondary milestone-action-btn"
                                                      style="font-size:12px;"
                                                      onclick="updateMilestoneStatus(<?= $ms['id'] ?>, 'pending', this)">
                                                  <i class="fas fa-undo me-1"></i>Reset
                                              </button>
                                              <?php elseif ($ms['status'] === 'completed'): ?>
                                              <button type="button"
                                                      class="btn btn-sm btn-outline-warning milestone-action-btn"
                                                      style="font-size:12px;"
                                                      onclick="updateMilestoneStatus(<?= $ms['id'] ?>, 'in_progress', this)">
                                                  <i class="fas fa-redo me-1"></i>Buka Kembali
                                              </button>
                                              <?php endif; ?>

                                            <!-- Delete Milestone -->
                                            <button type="button"
                                                    class="btn btn-xs btn-outline-danger"
                                                    onclick="deleteMilestone(<?= $ms['id'] ?>, '<?= addslashes(htmlspecialchars($ms['title'])) ?>')"
                                                    data-bs-toggle="tooltip" title="Hapus Milestone">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php else: ?>
                    <div class="empty-state" style="padding:32px 0;">
                        <i class="fas fa-tasks empty-state-icon"></i>
                        <h4>Belum Ada Milestone</h4>
                        <p>Tambahkan milestone untuk melacak progres pengerjaan pesanan ini.</p>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addMilestoneModal">
                            <i class="fas fa-plus me-2"></i>Tambah Milestone
                        </button>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Right Column: Status Management & Summary -->
        <div class="col-xl-4">

            <!-- Status Management -->
            <div class="admin-card mb-4">
                <div class="admin-card-header">
                    <h5 class="admin-card-title">
                        <i class="fas fa-exchange-alt"></i>
                        Ubah Status Pesanan
                    </h5>
                </div>
                <div class="admin-card-body">
                    <form method="POST">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
                        <input type="hidden" name="action" value="update_order_status">
                        <label class="form-label">Status</label>
                        <div class="input-group">
                            <select name="status" class="form-select">
                                <?php
                                $status_opts = [
                                    'pending'     => 'Menunggu',
                                    'in_progress' => 'Sedang Dikerjakan',
                                    'completed'   => 'Selesai',
                                    'cancelled'   => 'Dibatalkan',
                                ];
                                foreach ($status_opts as $sv => $sl):
                                ?>
                                <option value="<?= $sv ?>" <?= $order['status'] === $sv ? 'selected' : '' ?>>
                                    <?= $sl ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit" class="btn btn-secondary btn-sm">
                                <i class="fas fa-check"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Summary Card -->
            <div class="admin-card mb-4" style="background:linear-gradient(135deg,rgba(13,148,136,0.04),rgba(26,54,93,0.04));">
                <div class="admin-card-body">
                    <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.08em;color:#94a3b8;margin-bottom:14px;">
                        <i class="fas fa-info-circle me-1"></i>Ringkasan Pesanan
                    </div>
                    <div style="display:flex;flex-direction:column;gap:10px;font-size:13px;">
                        <div style="display:flex;justify-content:space-between;">
                            <span style="color:#64748b;">Kode Tracking:</span>
                            <code style="font-size:11.5px;font-weight:700;color:#1e293b;"><?= htmlspecialchars($order['tracking_code']) ?></code>
                        </div>
                        <div style="display:flex;justify-content:space-between;">
                            <span style="color:#64748b;">Status:</span>
                            <span class="badge bg-<?= getOrderStatusBadge($order['status']) ?>" style="font-size:11px;" id="summaryStatusBadge">
                                <?= getOrderStatusLabel($order['status']) ?>
                            </span>
                        </div>
                        <div style="display:flex;justify-content:space-between;">
                            <span style="color:#64748b;">Layanan:</span>
                            <strong style="font-size:12.5px;"><?= htmlspecialchars($service_labels[$order['service_type']] ?? $order['service_type']) ?></strong>
                        </div>
                        <div style="display:flex;justify-content:space-between;">
                            <span style="color:#64748b;">Paket:</span>
                            <strong><?= htmlspecialchars($package_labels[$order['package_tier']] ?? $order['package_tier']) ?></strong>
                        </div>
                        <div style="display:flex;justify-content:space-between;">
                            <span style="color:#64748b;">Milestone Selesai:</span>
                            <strong id="summaryMsCount"><?= $ms_completed ?>/<?= $ms_total ?></strong>
                        </div>
                        <div style="display:flex;justify-content:space-between;">
                            <span style="color:#64748b;">Progress:</span>
                            <strong id="summaryProgress"><?= $ms_percentage ?>%</strong>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="admin-card mb-4">
                <div class="admin-card-header">
                    <h5 class="admin-card-title">
                        <i class="fas fa-bolt"></i>
                        Aksi Cepat
                    </h5>
                </div>
                <div class="admin-card-body">
                    <a href="index.php?page=pesanan-form&id=<?= $id ?>"
                       class="btn btn-outline-primary w-100 mb-2">
                        <i class="fas fa-edit me-2"></i>Edit Detail Pesanan
                    </a>

                    <?php if (!empty($order['client_email'])): ?>
                    <a href="mailto:<?= htmlspecialchars($order['client_email']) ?>?subject=Update%20Pesanan%20<?= urlencode($order['tracking_code']) ?>&body=Halo%20<?= urlencode($order['client_name']) ?>,%0A%0ABerikut%20update%20mengenai%20pesanan%20Anda%20dengan%20kode%20<?= urlencode($order['tracking_code']) ?>.%0A%0ASalam,%0ATim%20OJS%20Developer%20Indonesia"
                       class="btn btn-outline-secondary w-100 mb-2">
                        <i class="fas fa-envelope me-2"></i>Kirim Update via Email
                    </a>
                    <?php endif; ?>

                    <?php if (!empty($order['client_phone'])): ?>
                    <?php
                    $wa2 = preg_replace('/[^0-9]/', '', $order['client_phone']);
                    if (substr($wa2, 0, 1) === '0') $wa2 = '62' . substr($wa2, 1);
                    ?>
                    <a href="https://wa.me/<?= $wa2 ?>?text=Halo%20<?= urlencode($order['client_name']) ?>,%20kami%20dari%20OJS%20Developer%20Indonesia%20ingin%20memberikan%20update%20pesanan%20Anda%20(<?= urlencode($order['tracking_code']) ?>)."
                       target="_blank"
                       class="btn btn-outline-success w-100 mb-2">
                        <i class="fab fa-whatsapp me-2"></i>Hubungi via WhatsApp
                    </a>
                    <?php endif; ?>

                    <!-- Delete Order -->
                    <form method="POST" action="index.php?page=pesanan"
                          onsubmit="return confirmDelete(this, '<?= addslashes(htmlspecialchars($order['client_name'])) ?>')">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" value="<?= $id ?>">
                        <button type="submit" class="btn btn-outline-danger w-100">
                            <i class="fas fa-trash me-2"></i>Hapus Pesanan Ini
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Milestone Modal -->
<div class="modal fade" id="addMilestoneModal" tabindex="-1" aria-labelledby="addMilestoneModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:16px;border:none;box-shadow:0 20px 60px rgba(0,0,0,0.15);">
            <div class="modal-header" style="border-bottom:1px solid #e2e8f0;padding:20px 24px;">
                <h5 class="modal-title" id="addMilestoneModalLabel">
                    <i class="fas fa-plus-circle me-2 text-primary"></i>Tambah Milestone Baru
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body" style="padding:24px;">
                <div class="mb-3">
                    <label class="form-label">Judul Milestone <span class="text-danger">*</span></label>
                    <input type="text" id="newMilestoneTitle" class="form-control"
                           placeholder="Contoh: Instalasi Plugin, Review Konten, dsb.">
                </div>
                <div class="mb-0">
                    <label class="form-label">Deskripsi (opsional)</label>
                    <textarea id="newMilestoneDescription" class="form-control" rows="3"
                              placeholder="Penjelasan singkat tentang milestone ini..."></textarea>
                </div>
            </div>
            <div class="modal-footer" style="border-top:1px solid #e2e8f0;padding:16px 24px;">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" onclick="addMilestone()">
                    <i class="fas fa-plus me-1"></i>Tambah Milestone
                </button>
            </div>
        </div>
    </div>
</div>

<?php
ob_start(); ?>
<script>
const CSRF_TOKEN  = <?= json_encode($csrf) ?>;
const ORDER_ID    = <?= $id ?>;
const AJAX_URL    = "index.php?page=pesanan-detail&id=" + ORDER_ID;

// ---- Copy Tracking Code ----
function copyTrackingCode() {
    const code = document.getElementById("trackingCodeDisplay").textContent.trim();
    const btn  = document.getElementById("copyTrackingBtn");

    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(code).then(function() {
            showCopiedState(btn);
        });
    } else {
        const ta = document.createElement("textarea");
        ta.value = code;
        ta.style.position = "fixed";
        ta.style.opacity = "0";
        document.body.appendChild(ta);
        ta.focus(); ta.select();
        try { document.execCommand("copy"); showCopiedState(btn); } catch(e) {}
        document.body.removeChild(ta);
    }
}

function showCopiedState(btn) {
    const orig = btn.innerHTML;
    btn.innerHTML = \'<i class="fas fa-check me-1"></i>Tersalin!\';
    btn.classList.add("btn-success");
    btn.classList.remove("btn-light");
    setTimeout(function() {
        btn.innerHTML = orig;
        btn.classList.remove("btn-success");
        btn.classList.add("btn-light");
    }, 2000);
}

// ---- Update Milestone Status (AJAX) ----
function updateMilestoneStatus(milestoneId, newStatus, btn) {
    // Disable all action buttons temporarily
    document.querySelectorAll(".milestone-action-btn").forEach(function(b) {
        b.disabled = true;
    });

    const formData = new FormData();
    formData.append("csrf_token",    CSRF_TOKEN);
    formData.append("action",        "update_milestone_status");
    formData.append("milestone_id",  milestoneId);
    formData.append("status",        newStatus);

    fetch(AJAX_URL, {
        method:  "POST",
        headers: { "X-Requested-With": "XMLHttpRequest" },
        body:    formData,
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) {
            // Show WA toast if notification was attempted
            if (data.wa_sent === true) {
                showWaToast('success', 'Notifikasi WA terkirim ke klien.');
            } else if (data.wa_sent === false && data.wa_message && data.wa_message !== 'skip' && data.wa_message !== 'Notifikasi WA dinonaktifkan.') {
                showWaToast('warning', 'WA gagal: ' + data.wa_message);
            }
            // Reload page to reflect updated timeline
            setTimeout(function() { window.location.reload(); }, data.wa_sent ? 1200 : 100);
        } else {
            alert("Gagal: " + (data.message || "Terjadi kesalahan."));
            document.querySelectorAll(".milestone-action-btn").forEach(function(b) {
                b.disabled = false;
            });
        }
    })
    .catch(function(err) {
        alert("Terjadi kesalahan koneksi.");
        document.querySelectorAll(".milestone-action-btn").forEach(function(b) {
            b.disabled = false;
        });
    });
}

    function markAllMilestonesComplete() {
          if (!confirm("Tandai SEMUA milestone sebagai selesai?")) return;
          var items = document.querySelectorAll(".milestone-item");
          var total = items.length;
          if (!total) return;
          var done = 0;
          items.forEach(function(item) {
              var msId = item.dataset.id;
              if (item.dataset.status !== "completed") {
                  fetch(AJAX_URL, {
                      method: "POST",
                      headers: {"Content-Type": "application/x-www-form-urlencoded", "X-Requested-With": "XMLHttpRequest"},
                      body: "csrf_token=" + encodeURIComponent(CSRF_TOKEN) + "&action=update_milestone_status&milestone_id=" + msId + "&status=completed"
                  }).then(function(r) { return r.json(); }).then(function() {
                      done++;
                      if (done >= total) location.reload();
                  });
              } else {
                  done++;
                  if (done >= total) location.reload();
              }
          });
      }
// ---- WA Toast Notification ----
function showWaToast(type, message) {
    var existing = document.getElementById("waToast");
    if (existing) existing.remove();

    var bgColor = type === 'success' ? '#16a34a' : '#d97706';
    var icon    = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-triangle';

    var toast = document.createElement('div');
    toast.id = 'waToast';
    toast.innerHTML = '<i class="fab fa-whatsapp me-2"></i><i class="fas ' + icon + ' me-2"></i>' + message;
    toast.style.cssText = 'position:fixed;top:20px;right:20px;z-index:99999;background:' + bgColor + ';color:#fff;padding:14px 24px;border-radius:12px;font-size:14px;font-weight:600;box-shadow:0 8px 32px rgba(0,0,0,0.15);animation:slideInRight 0.3s ease;max-width:400px;';
    document.body.appendChild(toast);

    setTimeout(function() { toast.style.opacity = '0'; toast.style.transition = 'opacity 0.3s'; }, 2500);
}

// ---- Add Milestone (AJAX) ----
function addMilestone() {
    const title       = document.getElementById("newMilestoneTitle").value.trim();
    const description = document.getElementById("newMilestoneDescription").value.trim();

    if (!title) {
        alert("Judul milestone wajib diisi.");
        document.getElementById("newMilestoneTitle").focus();
        return;
    }

    const btn = document.querySelector("#addMilestoneModal .btn-primary");
    btn.disabled = true;
    btn.innerHTML = \'<i class="fas fa-spinner fa-spin me-1"></i>Menyimpan...\';

    const formData = new FormData();
    formData.append("csrf_token",             CSRF_TOKEN);
    formData.append("action",                 "add_milestone");
    formData.append("milestone_title",        title);
    formData.append("milestone_description",  description);

    fetch(AJAX_URL, {
        method:  "POST",
        headers: { "X-Requested-With": "XMLHttpRequest" },
        body:    formData,
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) {
            // Reload to show new milestone in timeline
            window.location.reload();
        } else {
            alert("Gagal: " + (data.message || "Terjadi kesalahan."));
            btn.disabled = false;
            btn.innerHTML = \'<i class="fas fa-plus me-1"></i>Tambah Milestone\';
        }
    })
    .catch(function(err) {
        alert("Terjadi kesalahan koneksi.");
        btn.disabled = false;
        btn.innerHTML = \'<i class="fas fa-plus me-1"></i>Tambah Milestone\';
    });
}

// ---- Delete Milestone (AJAX) ----
function deleteMilestone(milestoneId, title) {
    if (!confirm("Hapus milestone \"" + title + "\"?\n\nTindakan ini tidak dapat dibatalkan.")) {
        return;
    }

    const formData = new FormData();
    formData.append("csrf_token",   CSRF_TOKEN);
    formData.append("action",       "delete_milestone");
    formData.append("milestone_id", milestoneId);

    fetch(AJAX_URL, {
        method:  "POST",
        headers: { "X-Requested-With": "XMLHttpRequest" },
        body:    formData,
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) {
            window.location.reload();
        } else {
            alert("Gagal: " + (data.message || "Terjadi kesalahan."));
        }
    })
    .catch(function(err) {
        alert("Terjadi kesalahan koneksi.");
    });
}

// Clear modal inputs when closed
document.getElementById("addMilestoneModal").addEventListener("hidden.bs.modal", function() {
    document.getElementById("newMilestoneTitle").value = "";
    document.getElementById("newMilestoneDescription").value = "";
});
</script>
<?php
$extra_js = ob_get_clean();
require_once ADMIN_PATH . '/includes/footer.php';
?>
