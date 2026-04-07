<?php
/**
 * Admin Konsultasi Detail View
 */

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$id) {
    flash('error', 'ID konsultasi tidak valid.');
    redirect('index.php?page=konsultasi');
}

// Load consultation
$konsultasi = null;
try {
    $konsultasi = fetch("SELECT * FROM consultations WHERE id = ?", [$id]);
} catch (Exception $e) {}

if (!$konsultasi) {
    flash('error', 'Data konsultasi tidak ditemukan.');
    redirect('index.php?page=konsultasi');
}

// Handle updates (status, priority, follow-up date, notes)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        flash('error', 'Token keamanan tidak valid.');
        redirect('index.php?page=konsultasi-detail&id=' . $id);
    }

    $action = sanitize($_POST['action'] ?? '');
    $data   = [];
    $all_statuses  = ['new', 'contacted', 'follow_up', 'negotiation', 'closed_won', 'closed_lost'];
    $all_priorities = ['low', 'medium', 'high'];

    if ($action === 'update_status') {
        $new_status = sanitize($_POST['status'] ?? '');
        if (in_array($new_status, $all_statuses)) {
            $data['status'] = $new_status;
        }
    }

    if ($action === 'update_priority') {
        $new_priority = sanitize($_POST['priority'] ?? '');
        if (in_array($new_priority, $all_priorities)) {
            $data['priority'] = $new_priority;
        }
    }

    if ($action === 'update_followup') {
        $fu_date = sanitize($_POST['follow_up_date'] ?? '');
        $data['follow_up_date'] = !empty($fu_date) ? $fu_date : null;
    }

    if ($action === 'add_note') {
        $new_note = trim($_POST['new_note'] ?? '');
        if (!empty($new_note)) {
            $timestamp   = date('d M Y H:i');
            $admin_name  = $_SESSION['admin_name'] ?? 'Admin';
            $note_entry  = "[{$timestamp} - {$admin_name}]\n" . sanitize($new_note);
            $existing    = $konsultasi['notes'] ?? '';
            $data['notes'] = $existing ? ($note_entry . "\n\n" . $existing) : $note_entry;
        }
    }

    if ($action === 'mark_won') {
        $data['status'] = 'closed_won';
    }

    if ($action === 'mark_lost') {
        $data['status'] = 'closed_lost';
    }

    if (!empty($data)) {
        try {
            update('consultations', $data, 'id = ?', [$id]);
            flash('success', 'Data konsultasi berhasil diperbarui.');
        } catch (Exception $e) {
            flash('error', 'Gagal memperbarui data: ' . $e->getMessage());
        }
    }

    redirect('index.php?page=konsultasi-detail&id=' . $id);
}

// Reload fresh data after redirect
try {
    $konsultasi = fetch("SELECT * FROM consultations WHERE id = ?", [$id]);
} catch (Exception $e) {}

$csrf = csrf_token();

$service_labels = [
    'setup_ojs'   => 'Setup & Instalasi OJS',
    'migrasi'     => 'Migrasi Jurnal',
    'kustomisasi' => 'Kustomisasi Tampilan',
    'pelatihan'   => 'Pelatihan OJS',
    'maintenance' => 'Maintenance & Support',
    'lainnya'     => 'Lainnya',
];

$status_labels  = ['new' => 'Baru', 'contacted' => 'Dihubungi', 'follow_up' => 'Follow Up', 'negotiation' => 'Negosiasi', 'closed_won' => 'Closed Won', 'closed_lost' => 'Closed Lost'];
$priority_labels = ['low' => 'Rendah', 'medium' => 'Sedang', 'high' => 'Tinggi'];

// Parse notes into timeline entries
$notes_timeline = [];
if (!empty($konsultasi['notes'])) {
    $entries = preg_split('/\n\n/', trim($konsultasi['notes']));
    foreach ($entries as $entry) {
        $entry = trim($entry);
        if (empty($entry)) continue;

        // Try to extract timestamp header
        if (preg_match('/^\[(.+?)\]\n(.+)$/s', $entry, $m)) {
            $notes_timeline[] = ['time' => $m[1], 'text' => trim($m[2])];
        } else {
            $notes_timeline[] = ['time' => '', 'text' => $entry];
        }
    }
}

require_once ADMIN_PATH . '/includes/header.php';
require_once ADMIN_PATH . '/includes/sidebar.php';

// WA phone cleanup
$wa_phone = preg_replace('/[^0-9]/', '', $konsultasi['phone'] ?? '');
if (substr($wa_phone, 0, 1) === '0') {
    $wa_phone = '62' . substr($wa_phone, 1);
}
?>

<div class="admin-content">
    <!-- Page Header -->
    <div class="page-header">
        <div class="page-header-left">
            <h2>Detail Konsultasi #<?= $id ?></h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="index.php?page=konsultasi">Konsultasi</a></li>
                    <li class="breadcrumb-item active"><?= htmlspecialchars($konsultasi['name']) ?></li>
                </ol>
            </nav>
        </div>
        <div class="page-header-actions">
            <a href="index.php?page=konsultasi" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i>Kembali ke Daftar
            </a>

            <!-- WhatsApp -->
            <?php if (!empty($konsultasi['phone'])): ?>
            <a href="https://wa.me/<?= $wa_phone ?>?text=Halo%20<?= urlencode($konsultasi['name']) ?>,%20kami%20dari%20OJS%20Developer%20Indonesia."
               target="_blank"
               class="btn btn-success">
                <i class="fab fa-whatsapp me-1"></i>WhatsApp
            </a>
            <?php endif; ?>

            <!-- Email -->
            <a href="mailto:<?= htmlspecialchars($konsultasi['email']) ?>?subject=Konsultasi%20OJS%20-%20Tindak%20Lanjut&body=Halo%20<?= urlencode($konsultasi['name']) ?>,%0A%0AKami%20dari%20OJS%20Developer%20Indonesia%20ingin%20menindaklanjuti%20permintaan%20konsultasi%20Anda.%0A%0ASalam,%0ATim%20OJS%20Developer%20Indonesia"
               class="btn btn-outline-primary">
                <i class="fas fa-envelope me-1"></i>Balas via Email
            </a>
        </div>
    </div>

    <div class="row g-4">
        <!-- Left Column: Full Info -->
        <div class="col-xl-8">
            <!-- Contact Information -->
            <div class="admin-card mb-4">
                <div class="admin-card-header">
                    <h5 class="admin-card-title">
                        <i class="fas fa-user"></i>
                        Informasi Kontak
                    </h5>
                    <div style="display:flex;gap:6px;">
                        <span class="badge badge-status-<?= htmlspecialchars($konsultasi['status']) ?>" style="font-size:12px;padding:6px 14px;">
                            <?= htmlspecialchars($status_labels[$konsultasi['status']] ?? $konsultasi['status']) ?>
                        </span>
                        <span class="badge badge-priority-<?= htmlspecialchars($konsultasi['priority']) ?>" style="font-size:12px;padding:6px 14px;">
                            <i class="fas fa-flag me-1"></i>
                            <?= htmlspecialchars($priority_labels[$konsultasi['priority']] ?? $konsultasi['priority']) ?>
                        </span>
                    </div>
                </div>
                <div class="admin-card-body">
                    <div class="row g-0">
                        <div class="col-md-6">
                            <div class="detail-item">
                                <span class="detail-label">Nama Lengkap</span>
                                <span class="detail-value"><?= htmlspecialchars($konsultasi['name']) ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Alamat Email</span>
                                <span class="detail-value">
                                    <a href="mailto:<?= htmlspecialchars($konsultasi['email']) ?>" style="color:var(--secondary);">
                                        <?= htmlspecialchars($konsultasi['email']) ?>
                                    </a>
                                </span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">No. Telepon / WhatsApp</span>
                                <span class="detail-value">
                                    <?php if (!empty($konsultasi['phone'])): ?>
                                        <a href="https://wa.me/<?= $wa_phone ?>" target="_blank" style="color:var(--secondary);">
                                            <i class="fab fa-whatsapp me-1"></i>
                                            <?= htmlspecialchars($konsultasi['phone']) ?>
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
                                <span class="detail-value"><?= htmlspecialchars($konsultasi['institution'] ?? '-') ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Jenis Layanan</span>
                                <span class="detail-value">
                                    <?= htmlspecialchars($service_labels[$konsultasi['service_type']] ?? $konsultasi['service_type']) ?>
                                </span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Estimasi Budget</span>
                                <span class="detail-value"><?= htmlspecialchars($konsultasi['budget_range'] ?? '-') ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Message -->
            <div class="admin-card mb-4">
                <div class="admin-card-header">
                    <h5 class="admin-card-title">
                        <i class="fas fa-comment-dots"></i>
                        Pesan / Kebutuhan
                    </h5>
                </div>
                <div class="admin-card-body">
                    <?php if (!empty($konsultasi['message'])): ?>
                    <div style="background:#f8fafc;border-radius:10px;padding:20px;border-left:4px solid var(--secondary);font-size:14px;line-height:1.7;color:#374151;white-space:pre-line;">
                        <?= htmlspecialchars($konsultasi['message']) ?>
                    </div>
                    <?php else: ?>
                    <p style="color:#94a3b8;font-style:italic;">Tidak ada pesan tambahan.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Timestamps -->
            <div class="admin-card mb-4">
                <div class="admin-card-header">
                    <h5 class="admin-card-title">
                        <i class="fas fa-clock"></i>
                        Riwayat Waktu
                    </h5>
                </div>
                <div class="admin-card-body">
                    <div class="row g-3">
                        <div class="col-sm-4">
                            <div style="text-align:center;padding:16px;background:#f8fafc;border-radius:10px;border:1px solid #e2e8f0;">
                                <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.08em;color:#94a3b8;margin-bottom:6px;">Tanggal Masuk</div>
                                <div style="font-size:14px;font-weight:700;color:#1e293b;">
                                    <?= date('d M Y', strtotime($konsultasi['created_at'])) ?>
                                </div>
                                <div style="font-size:12px;color:#64748b;">
                                    <?= date('H:i', strtotime($konsultasi['created_at'])) ?> WIB
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div style="text-align:center;padding:16px;background:#f8fafc;border-radius:10px;border:1px solid #e2e8f0;">
                                <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.08em;color:#94a3b8;margin-bottom:6px;">Terakhir Diperbarui</div>
                                <div style="font-size:14px;font-weight:700;color:#1e293b;">
                                    <?= date('d M Y', strtotime($konsultasi['updated_at'])) ?>
                                </div>
                                <div style="font-size:12px;color:#64748b;">
                                    <?= date('H:i', strtotime($konsultasi['updated_at'])) ?> WIB
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div style="text-align:center;padding:16px;background:<?= !empty($konsultasi['follow_up_date']) && strtotime($konsultasi['follow_up_date']) < time() && !in_array($konsultasi['status'], ['closed_won','closed_lost']) ? '#fff8f0' : '#f8fafc' ?>;border-radius:10px;border:1px solid <?= !empty($konsultasi['follow_up_date']) && strtotime($konsultasi['follow_up_date']) < time() && !in_array($konsultasi['status'], ['closed_won','closed_lost']) ? '#fed7aa' : '#e2e8f0' ?>;">
                                <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.08em;color:#94a3b8;margin-bottom:6px;">Jadwal Follow Up</div>
                                <?php if (!empty($konsultasi['follow_up_date'])): ?>
                                    <div style="font-size:14px;font-weight:700;color:#1e293b;">
                                        <?= date('d M Y', strtotime($konsultasi['follow_up_date'])) ?>
                                    </div>
                                    <?php if (strtotime($konsultasi['follow_up_date']) < time() && !in_array($konsultasi['status'], ['closed_won','closed_lost'])): ?>
                                    <div style="font-size:11px;color:#dc2626;font-weight:700;">
                                        <i class="fas fa-exclamation-triangle me-1"></i>OVERDUE
                                    </div>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <div style="font-size:13px;color:#94a3b8;">Belum dijadwalkan</div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Notes Timeline -->
            <div class="admin-card">
                <div class="admin-card-header">
                    <h5 class="admin-card-title">
                        <i class="fas fa-history"></i>
                        Catatan & Riwayat
                    </h5>
                </div>
                <div class="admin-card-body">
                    <!-- Add Note Form -->
                    <form method="POST" class="mb-4">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
                        <input type="hidden" name="action" value="add_note">
                        <div class="mb-2">
                            <label class="form-label">Tambah Catatan Baru</label>
                            <textarea name="new_note" class="form-control" rows="3"
                                      placeholder="Tulis catatan tindakan yang telah dilakukan, hasil komunikasi, dsb..."></textarea>
                        </div>
                        <button type="submit" class="btn btn-sm btn-secondary">
                            <i class="fas fa-plus me-1"></i>Tambah Catatan
                        </button>
                    </form>

                    <!-- Timeline -->
                    <?php if (!empty($notes_timeline)): ?>
                    <div class="timeline">
                        <?php foreach ($notes_timeline as $note): ?>
                        <div class="timeline-item">
                            <div class="timeline-dot"></div>
                            <div class="timeline-content">
                                <?php if (!empty($note['time'])): ?>
                                <div class="timeline-time">
                                    <i class="fas fa-clock me-1"></i><?= htmlspecialchars($note['time']) ?>
                                </div>
                                <?php endif; ?>
                                <div class="timeline-text" style="white-space:pre-line;">
                                    <?= htmlspecialchars($note['text']) ?>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php else: ?>
                    <div style="text-align:center;padding:24px;color:#94a3b8;">
                        <i class="fas fa-sticky-note fa-2x mb-2 d-block"></i>
                        <p style="font-size:13px;">Belum ada catatan. Tambahkan catatan untuk mendokumentasikan perkembangan konsultasi ini.</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Right Column: CRM Actions -->
        <div class="col-xl-4">
            <!-- Status Management -->
            <div class="admin-card mb-4">
                <div class="admin-card-header">
                    <h5 class="admin-card-title">
                        <i class="fas fa-tasks"></i>
                        Manajemen Status
                    </h5>
                </div>
                <div class="admin-card-body">
                    <!-- Status -->
                    <form method="POST" class="mb-3">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
                        <input type="hidden" name="action" value="update_status">
                        <label class="form-label">Status Konsultasi</label>
                        <div class="input-group">
                            <select name="status" class="form-select">
                                <?php
                                $all_statuses = ['new' => 'Baru', 'contacted' => 'Dihubungi', 'follow_up' => 'Follow Up', 'negotiation' => 'Negosiasi', 'closed_won' => 'Closed Won', 'closed_lost' => 'Closed Lost'];
                                foreach ($all_statuses as $sk => $sv):
                                ?>
                                <option value="<?= $sk ?>" <?= $konsultasi['status'] === $sk ? 'selected' : '' ?>>
                                    <?= $sv ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit" class="btn btn-secondary btn-sm">
                                <i class="fas fa-check"></i>
                            </button>
                        </div>
                    </form>

                    <!-- Priority -->
                    <form method="POST" class="mb-3">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
                        <input type="hidden" name="action" value="update_priority">
                        <label class="form-label">Prioritas</label>
                        <div class="input-group">
                            <select name="priority" class="form-select">
                                <option value="low"    <?= $konsultasi['priority'] === 'low'    ? 'selected' : '' ?>>Rendah</option>
                                <option value="medium" <?= $konsultasi['priority'] === 'medium' ? 'selected' : '' ?>>Sedang</option>
                                <option value="high"   <?= $konsultasi['priority'] === 'high'   ? 'selected' : '' ?>>Tinggi</option>
                            </select>
                            <button type="submit" class="btn btn-secondary btn-sm">
                                <i class="fas fa-check"></i>
                            </button>
                        </div>
                    </form>

                    <!-- Follow-up Date -->
                    <form method="POST" class="mb-3">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
                        <input type="hidden" name="action" value="update_followup">
                        <label class="form-label">Jadwal Follow Up</label>
                        <div class="input-group">
                            <input type="date" name="follow_up_date" class="form-control"
                                   value="<?= htmlspecialchars($konsultasi['follow_up_date'] ?? '') ?>">
                            <button type="submit" class="btn btn-secondary btn-sm">
                                <i class="fas fa-check"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Quick Close Actions -->
            <div class="admin-card mb-4">
                <div class="admin-card-header">
                    <h5 class="admin-card-title">
                        <i class="fas fa-bolt"></i>
                        Aksi Cepat
                    </h5>
                </div>
                <div class="admin-card-body">
                    <!-- Mark Won -->
                    <form method="POST" class="mb-2">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
                        <input type="hidden" name="action" value="mark_won">
                        <button type="submit" class="btn btn-success w-100"
                                onclick="return confirm('Tandai konsultasi ini sebagai Closed Won (berhasil)?')"
                                <?= in_array($konsultasi['status'], ['closed_won','closed_lost']) ? 'disabled' : '' ?>>
                            <i class="fas fa-trophy me-2"></i>Tandai Berhasil (Closed Won)
                        </button>
                    </form>

                    <!-- Mark Lost -->
                    <form method="POST" class="mb-2">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
                        <input type="hidden" name="action" value="mark_lost">
                        <button type="submit" class="btn btn-outline-danger w-100"
                                onclick="return confirm('Tandai konsultasi ini sebagai Closed Lost (tidak berhasil)?')"
                                <?= in_array($konsultasi['status'], ['closed_won','closed_lost']) ? 'disabled' : '' ?>>
                            <i class="fas fa-times-circle me-2"></i>Tandai Tidak Berhasil
                        </button>
                    </form>

                    <?php if (!empty($konsultasi['phone'])): ?>
                    <a href="https://wa.me/<?= $wa_phone ?>?text=Halo%20<?= urlencode($konsultasi['name']) ?>,%20kami%20dari%20OJS%20Developer%20Indonesia%20ingin%20menindaklanjuti%20konsultasi%20Anda%20mengenai%20layanan%20<?= urlencode($service_labels[$konsultasi['service_type']] ?? '') ?>."
                       target="_blank"
                       class="btn btn-outline-success w-100 mb-2">
                        <i class="fab fa-whatsapp me-2"></i>Hubungi via WhatsApp
                    </a>
                    <?php endif; ?>

                    <a href="mailto:<?= htmlspecialchars($konsultasi['email']) ?>?subject=Re:%20Konsultasi%20OJS&body=Halo%20<?= urlencode($konsultasi['name']) ?>,%0A%0A"
                       class="btn btn-outline-primary w-100">
                        <i class="fas fa-envelope me-2"></i>Balas via Email
                    </a>
                </div>
            </div>

            <!-- Consultation Summary -->
            <div class="admin-card" style="background:linear-gradient(135deg,rgba(13,148,136,0.04),rgba(26,54,93,0.04));">
                <div class="admin-card-body">
                    <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.08em;color:#94a3b8;margin-bottom:14px;">
                        <i class="fas fa-info-circle me-1"></i>Ringkasan
                    </div>
                    <div style="display:flex;flex-direction:column;gap:8px;font-size:13px;">
                        <div style="display:flex;justify-content:space-between;">
                            <span style="color:#64748b;">ID Konsultasi:</span>
                            <strong>#<?= $id ?></strong>
                        </div>
                        <div style="display:flex;justify-content:space-between;">
                            <span style="color:#64748b;">Status:</span>
                            <span class="badge badge-status-<?= $konsultasi['status'] ?>">
                                <?= $status_labels[$konsultasi['status']] ?? $konsultasi['status'] ?>
                            </span>
                        </div>
                        <div style="display:flex;justify-content:space-between;">
                            <span style="color:#64748b;">Prioritas:</span>
                            <span class="badge badge-priority-<?= $konsultasi['priority'] ?>">
                                <?= $priority_labels[$konsultasi['priority']] ?? $konsultasi['priority'] ?>
                            </span>
                        </div>
                        <div style="display:flex;justify-content:space-between;">
                            <span style="color:#64748b;">Catatan:</span>
                            <strong><?= count($notes_timeline) ?> entri</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once ADMIN_PATH . '/includes/footer.php'; ?>
