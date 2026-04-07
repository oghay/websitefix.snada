<?php
/**
 * Admin Pesanan (Order Management) List
 */

// Handle delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        flash('error', 'Token keamanan tidak valid.');
        redirect('index.php?page=pesanan');
    }

    $id = (int)($_POST['id'] ?? 0);
    if ($id > 0) {
        try {
            // Delete milestones first (FK constraint)
            delete('order_milestones', 'order_id = ?', [$id]);
            delete('orders', 'id = ?', [$id]);
            flash('success', 'Pesanan berhasil dihapus.');
        } catch (Exception $e) {
            flash('error', 'Gagal menghapus pesanan: ' . $e->getMessage());
        }
    }
    redirect('index.php?page=pesanan');
}

// Get filter
$filter_status = isset($_GET['status']) ? sanitize($_GET['status']) : 'all';
$valid_statuses = ['pending', 'in_progress', 'completed', 'cancelled'];

// Get counts for stat cards
$total_count      = 0;
$pending_count    = 0;
$in_progress_count = 0;
$completed_count  = 0;
$cancelled_count  = 0;

try {
    $r = fetch("SELECT COUNT(*) as cnt FROM orders");
    $total_count = $r ? (int)$r['cnt'] : 0;

    $r = fetch("SELECT COUNT(*) as cnt FROM orders WHERE status = 'pending'");
    $pending_count = $r ? (int)$r['cnt'] : 0;

    $r = fetch("SELECT COUNT(*) as cnt FROM orders WHERE status = 'in_progress'");
    $in_progress_count = $r ? (int)$r['cnt'] : 0;

    $r = fetch("SELECT COUNT(*) as cnt FROM orders WHERE status = 'completed'");
    $completed_count = $r ? (int)$r['cnt'] : 0;

    $r = fetch("SELECT COUNT(*) as cnt FROM orders WHERE status = 'cancelled'");
    $cancelled_count = $r ? (int)$r['cnt'] : 0;
} catch (Exception $e) {}

// Build query with filter
$query  = "SELECT * FROM orders";
$params = [];
if ($filter_status !== 'all' && in_array($filter_status, $valid_statuses)) {
    $query .= " WHERE status = ?";
    $params[] = $filter_status;
}
$query .= " ORDER BY created_at DESC";

$orders = [];
try {
    $orders = fetchAll($query, $params);
} catch (Exception $e) {}

$service_labels = [
    'setup_ojs'       => 'Setup OJS',
    'migrasi'         => 'Migrasi Jurnal',
    'kustomisasi'     => 'Kustomisasi',
    'pelatihan'       => 'Pelatihan',
    'maintenance'     => 'Maintenance',
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

$status_tab_labels = [
    'all'         => 'Semua',
    'pending'     => 'Menunggu',
    'in_progress' => 'Dikerjakan',
    'completed'   => 'Selesai',
    'cancelled'   => 'Dibatalkan',
];

$status_counts = [
    'all'         => $total_count,
    'pending'     => $pending_count,
    'in_progress' => $in_progress_count,
    'completed'   => $completed_count,
    'cancelled'   => $cancelled_count,
];

$csrf = csrf_token();

require_once ADMIN_PATH . '/includes/header.php';
require_once ADMIN_PATH . '/includes/sidebar.php';
?>

<div class="admin-content">
    <!-- Page Header -->
    <div class="page-header">
        <div class="page-header-left">
            <h2>Manajemen Pesanan</h2>
            <p>Kelola semua pesanan layanan dari klien, lacak progres pengerjaan.</p>
        </div>
        <div class="page-header-actions">
            <a href="index.php?page=pesanan-form" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i>Buat Pesanan Baru
            </a>
        </div>
    </div>

    <!-- Stat Cards -->
    <div class="row g-4 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="stat-card stat-card-primary">
                <div class="stat-icon">
                    <i class="fas fa-clipboard-list"></i>
                </div>
                <div class="stat-info">
                    <div class="stat-value"><?= $total_count ?></div>
                    <div class="stat-label">Total Pesanan</div>
                    <div class="stat-change">
                        <i class="fas fa-list"></i>
                        <span style="font-size:11px;">Semua pesanan</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="stat-card stat-card-warning">
                <div class="stat-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-info">
                    <div class="stat-value"><?= $pending_count ?></div>
                    <div class="stat-label">Menunggu</div>
                    <div class="stat-change <?= $pending_count > 0 ? 'up' : '' ?>">
                        <?php if ($pending_count > 0): ?>
                            <i class="fas fa-exclamation-circle"></i>
                            <span style="font-size:11px;">Perlu ditindaklanjuti</span>
                        <?php else: ?>
                            <span style="font-size:11px;">Tidak ada yang pending</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="stat-card stat-card-info">
                <div class="stat-icon">
                    <i class="fas fa-spinner"></i>
                </div>
                <div class="stat-info">
                    <div class="stat-value"><?= $in_progress_count ?></div>
                    <div class="stat-label">Sedang Dikerjakan</div>
                    <div class="stat-change">
                        <i class="fas fa-tools"></i>
                        <span style="font-size:11px;">In progress</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="stat-card stat-card-success">
                <div class="stat-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-info">
                    <div class="stat-value"><?= $completed_count ?></div>
                    <div class="stat-label">Selesai</div>
                    <div class="stat-change up">
                        <i class="fas fa-trophy"></i>
                        <span style="font-size:11px;">Pesanan selesai</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Status Filter Tabs -->
    <div class="status-filter-tabs">
        <?php
        $tab_icons = [
            'all'         => 'fa-list',
            'pending'     => 'fa-clock',
            'in_progress' => 'fa-spinner',
            'completed'   => 'fa-check-circle',
            'cancelled'   => 'fa-times-circle',
        ];
        $tab_colors = [
            'pending'     => ['color' => '#d97706'],
            'in_progress' => ['color' => '#0e7490'],
            'completed'   => ['color' => '#15803d'],
            'cancelled'   => ['color' => '#991b1b'],
        ];
        foreach ($status_tab_labels as $s => $label):
            $is_active = ($filter_status === $s);
            $href      = ($s === 'all') ? 'index.php?page=pesanan' : 'index.php?page=pesanan&status=' . $s;
        ?>
        <a href="<?= $href ?>"
           class="status-filter-btn <?= $is_active ? 'active' : '' ?>"
           <?php if (!$is_active && $s !== 'all'): ?>
           style="border-color:<?= $tab_colors[$s]['color'] ?>30;"
           <?php endif; ?>>
            <i class="fas <?= $tab_icons[$s] ?>"></i>
            <?= $label ?>
            <span class="status-filter-count"><?= $status_counts[$s] ?></span>
        </a>
        <?php endforeach; ?>
    </div>

    <!-- Orders Table -->
    <div class="admin-card">
        <div class="admin-card-header">
            <h5 class="admin-card-title">
                <i class="fas fa-clipboard-list"></i>
                <?= $filter_status === 'all' ? 'Semua Pesanan' : $status_tab_labels[$filter_status] ?>
                <span class="badge" style="background:#e2e8f0;color:#475569;font-size:11px;margin-left:4px;">
                    <?= count($orders) ?> data
                </span>
            </h5>
        </div>
        <div class="admin-card-body">
            <?php if (!empty($orders)): ?>
            <div class="table-responsive">
                <table class="table admin-datatable" id="pesananTable">
                    <thead>
                        <tr>
                            <th width="40">#</th>
                            <th>Kode Tracking</th>
                            <th>Klien</th>
                            <th>Layanan</th>
                            <th>Paket</th>
                            <th>Status</th>
                            <th>Tanggal</th>
                            <th width="130">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $i => $o): ?>
                        <tr>
                            <td><?= $i + 1 ?></td>
                            <td>
                                <div style="display:flex;align-items:center;gap:6px;">
                                    <code style="font-size:12px;background:#f1f5f9;padding:3px 8px;border-radius:6px;color:#1e293b;font-weight:600;letter-spacing:0.03em;">
                                        <?= htmlspecialchars($o['tracking_code']) ?>
                                    </code>
                                    <button type="button"
                                            class="btn btn-xs btn-outline-secondary copy-btn"
                                            data-copy="<?= htmlspecialchars($o['tracking_code']) ?>"
                                            data-bs-toggle="tooltip" title="Salin kode tracking"
                                            onclick="copyToClipboard(this)"
                                            style="padding:2px 6px;font-size:11px;">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                </div>
                            </td>
                            <td>
                                <div style="font-weight:600;"><?= htmlspecialchars($o['client_name']) ?></div>
                                <?php if (!empty($o['client_email'])): ?>
                                <div style="font-size:11px;color:#64748b;"><?= htmlspecialchars($o['client_email']) ?></div>
                                <?php endif; ?>
                                <?php if (!empty($o['client_institution'])): ?>
                                <div style="font-size:11px;color:#94a3b8;">
                                    <i class="fas fa-university fa-xs me-1"></i><?= htmlspecialchars($o['client_institution']) ?>
                                </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span style="font-size:12.5px;">
                                    <?= htmlspecialchars($service_labels[$o['service_type']] ?? $o['service_type']) ?>
                                </span>
                            </td>
                            <td>
                                <span style="font-size:12px;color:#64748b;">
                                    <?= htmlspecialchars($package_labels[$o['package_tier']] ?? $o['package_tier']) ?>
                                </span>
                            </td>
                            <td>
                                <?php
                                $badge_class = getOrderStatusBadge($o['status']);
                                ?>
                                <span class="badge bg-<?= $badge_class ?>" style="font-size:11.5px;padding:5px 10px;border-radius:20px;">
                                    <?= getOrderStatusLabel($o['status']) ?>
                                </span>
                            </td>
                            <td style="font-size:12px;color:#64748b;white-space:nowrap;">
                                <?= function_exists('formatDate') ? formatDate($o['created_at']) : date('d M Y', strtotime($o['created_at'])) ?>
                            </td>
                            <td>
                                <div class="action-btns">
                                    <!-- Detail -->
                                    <a href="index.php?page=pesanan-detail&id=<?= $o['id'] ?>"
                                       class="btn btn-xs btn-primary"
                                       data-bs-toggle="tooltip" title="Lihat Detail & Milestone">
                                        <i class="fas fa-eye"></i>
                                    </a>

                                    <!-- Edit -->
                                    <a href="index.php?page=pesanan-form&id=<?= $o['id'] ?>"
                                       class="btn btn-xs btn-outline-secondary"
                                       data-bs-toggle="tooltip" title="Edit Pesanan">
                                        <i class="fas fa-edit"></i>
                                    </a>

                                    <!-- Delete -->
                                    <form method="POST" style="display:inline;"
                                          onsubmit="return confirmDelete(this, '<?= addslashes(htmlspecialchars($o['client_name'])) ?>')">
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?= $o['id'] ?>">
                                        <button type="submit" class="btn btn-xs btn-outline-danger"
                                                data-bs-toggle="tooltip" title="Hapus Pesanan">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-clipboard-list empty-state-icon"></i>
                <h4>Tidak Ada Data Pesanan</h4>
                <p>
                    <?php if ($filter_status !== 'all'): ?>
                        Tidak ada pesanan dengan status "<?= $status_tab_labels[$filter_status] ?>".
                    <?php else: ?>
                        Belum ada pesanan yang masuk. Buat pesanan pertama sekarang.
                    <?php endif; ?>
                </p>
                <?php if ($filter_status !== 'all'): ?>
                <a href="index.php?page=pesanan" class="btn btn-outline-secondary me-2">
                    <i class="fas fa-list me-1"></i>Lihat Semua
                </a>
                <?php endif; ?>
                <a href="index.php?page=pesanan-form" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i>Buat Pesanan Baru
                </a>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
$extra_js = '
<script>
function copyToClipboard(btn) {
    const text = btn.getAttribute("data-copy");
    if (!text) return;

    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(text).then(function() {
            showCopied(btn);
        });
    } else {
        // Fallback
        const ta = document.createElement("textarea");
        ta.value = text;
        ta.style.position = "fixed";
        ta.style.opacity = "0";
        document.body.appendChild(ta);
        ta.focus();
        ta.select();
        try { document.execCommand("copy"); showCopied(btn); } catch(e) {}
        document.body.removeChild(ta);
    }
}

function showCopied(btn) {
    const icon = btn.querySelector("i");
    const orig = icon.className;
    icon.className = "fas fa-check";
    btn.classList.add("btn-success");
    btn.classList.remove("btn-outline-secondary");
    setTimeout(function() {
        icon.className = orig;
        btn.classList.remove("btn-success");
        btn.classList.add("btn-outline-secondary");
    }, 1500);
}
</script>
';
require_once ADMIN_PATH . '/includes/footer.php';
?>
