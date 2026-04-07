<?php
/**
 * Admin Export - Konsultasi Data to CSV
 */

// Handle CSV Export
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'export') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        flash('error', 'Token keamanan tidak valid.');
        redirect('index.php?page=export');
    }

    $date_from  = sanitize($_POST['date_from'] ?? '');
    $date_to    = sanitize($_POST['date_to'] ?? '');
    $status_f   = sanitize($_POST['status'] ?? '');
    $priority_f = sanitize($_POST['priority'] ?? '');

    // Build query
    $where   = [];
    $params  = [];

    if (!empty($date_from)) {
        $where[] = "DATE(created_at) >= ?";
        $params[] = $date_from;
    }
    if (!empty($date_to)) {
        $where[] = "DATE(created_at) <= ?";
        $params[] = $date_to;
    }

    $valid_statuses  = ['new', 'contacted', 'follow_up', 'negotiation', 'closed_won', 'closed_lost'];
    $valid_priorities = ['low', 'medium', 'high'];

    if (!empty($status_f) && in_array($status_f, $valid_statuses)) {
        $where[] = "status = ?";
        $params[] = $status_f;
    }
    if (!empty($priority_f) && in_array($priority_f, $valid_priorities)) {
        $where[] = "priority = ?";
        $params[] = $priority_f;
    }

    $sql = "SELECT * FROM consultations";
    if ($where) {
        $sql .= " WHERE " . implode(" AND ", $where);
    }
    $sql .= " ORDER BY created_at DESC";

    $rows = [];
    try {
        $rows = fetchAll($sql, $params);
    } catch (Exception $e) {
        flash('error', 'Gagal mengambil data: ' . $e->getMessage());
        redirect('index.php?page=export');
    }

    // Service labels
    $service_labels = [
        'setup_ojs'   => 'Setup & Instalasi OJS',
        'migrasi'     => 'Migrasi Jurnal',
        'kustomisasi' => 'Kustomisasi Tampilan',
        'pelatihan'   => 'Pelatihan OJS',
        'maintenance' => 'Maintenance & Support',
        'lainnya'     => 'Lainnya',
    ];
    $status_labels   = ['new' => 'Baru', 'contacted' => 'Dihubungi', 'follow_up' => 'Follow Up', 'negotiation' => 'Negosiasi', 'closed_won' => 'Closed Won', 'closed_lost' => 'Closed Lost'];
    $priority_labels = ['low' => 'Rendah', 'medium' => 'Sedang', 'high' => 'Tinggi'];

    // Generate CSV
    $filename = 'konsultasi_export_' . date('Ymd_His') . '.csv';

    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Pragma: no-cache');
    header('Expires: 0');

    // BOM for Excel UTF-8
    echo "\xEF\xBB\xBF";

    $output = fopen('php://output', 'w');

    // Headers
    fputcsv($output, [
        'ID',
        'Nama',
        'Email',
        'Telepon',
        'Institusi',
        'Jenis Layanan',
        'Estimasi Budget',
        'Pesan',
        'Status',
        'Prioritas',
        'Catatan',
        'Jadwal Follow Up',
        'Tanggal Masuk',
        'Terakhir Diperbarui',
    ]);

    foreach ($rows as $row) {
        fputcsv($output, [
            $row['id'],
            $row['name'],
            $row['email'],
            $row['phone'] ?? '',
            $row['institution'] ?? '',
            $service_labels[$row['service_type']] ?? $row['service_type'],
            $row['budget_range'] ?? '',
            $row['message'] ?? '',
            $status_labels[$row['status']] ?? $row['status'],
            $priority_labels[$row['priority']] ?? $row['priority'],
            $row['notes'] ?? '',
            $row['follow_up_date'] ?? '',
            date('d/m/Y H:i', strtotime($row['created_at'])),
            date('d/m/Y H:i', strtotime($row['updated_at'])),
        ]);
    }

    fclose($output);
    exit;
}

// --- Page Display ---

// Get filter params
$filter_date_from  = sanitize($_GET['date_from'] ?? '');
$filter_date_to    = sanitize($_GET['date_to'] ?? '');
$filter_status_val = sanitize($_GET['status'] ?? '');
$filter_priority   = sanitize($_GET['priority'] ?? '');

// Build preview query
$where_p  = [];
$params_p = [];

$valid_statuses  = ['new', 'contacted', 'follow_up', 'negotiation', 'closed_won', 'closed_lost'];
$valid_priorities = ['low', 'medium', 'high'];

if (!empty($filter_date_from)) {
    $where_p[] = "DATE(created_at) >= ?";
    $params_p[] = $filter_date_from;
}
if (!empty($filter_date_to)) {
    $where_p[] = "DATE(created_at) <= ?";
    $params_p[] = $filter_date_to;
}
if (!empty($filter_status_val) && in_array($filter_status_val, $valid_statuses)) {
    $where_p[] = "status = ?";
    $params_p[] = $filter_status_val;
}
if (!empty($filter_priority) && in_array($filter_priority, $valid_priorities)) {
    $where_p[] = "priority = ?";
    $params_p[] = $filter_priority;
}

$sql_p = "SELECT * FROM consultations";
if ($where_p) {
    $sql_p .= " WHERE " . implode(" AND ", $where_p);
}
$sql_p .= " ORDER BY created_at DESC";

$preview_data = [];
try {
    $preview_data = fetchAll($sql_p, $params_p);
} catch (Exception $e) {}

$total_all = 0;
try {
    $r = fetch("SELECT COUNT(*) as cnt FROM consultations");
    $total_all = $r ? (int)$r['cnt'] : 0;
} catch (Exception $e) {}

$service_labels = [
    'setup_ojs'   => 'Setup OJS',
    'migrasi'     => 'Migrasi',
    'kustomisasi' => 'Kustomisasi',
    'pelatihan'   => 'Pelatihan',
    'maintenance' => 'Maintenance',
    'lainnya'     => 'Lainnya',
];
$status_labels   = ['new' => 'Baru', 'contacted' => 'Dihubungi', 'follow_up' => 'Follow Up', 'negotiation' => 'Negosiasi', 'closed_won' => 'Closed Won', 'closed_lost' => 'Closed Lost'];
$priority_labels = ['low' => 'Rendah', 'medium' => 'Sedang', 'high' => 'Tinggi'];

$csrf = csrf_token();

require_once ADMIN_PATH . '/includes/header.php';
require_once ADMIN_PATH . '/includes/sidebar.php';
?>

<div class="admin-content">
    <!-- Page Header -->
    <div class="page-header">
        <div class="page-header-left">
            <h2>Export Data</h2>
            <p>Unduh data konsultasi dalam format CSV untuk analisis lebih lanjut.</p>
        </div>
        <div class="page-header-actions">
            <!-- Export Semua -->
            <form method="POST" style="display:inline;">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
                <input type="hidden" name="action" value="export">
                <button type="submit" class="btn btn-outline-secondary">
                    <i class="fas fa-database me-1"></i>Export Semua (<?= $total_all ?> data)
                </button>
            </form>
        </div>
    </div>

    <div class="row g-4">
        <!-- Filter Form -->
        <div class="col-xl-4">
            <div class="admin-card">
                <div class="admin-card-header">
                    <h5 class="admin-card-title">
                        <i class="fas fa-filter"></i>
                        Filter Export
                    </h5>
                </div>
                <div class="admin-card-body">
                    <form method="GET" id="filterForm">
                        <input type="hidden" name="page" value="export">

                        <!-- Date Range -->
                        <div class="mb-3">
                            <label class="form-label">Tanggal Masuk (Dari)</label>
                            <input type="date" name="date_from" class="form-control"
                                   value="<?= htmlspecialchars($filter_date_from) ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Tanggal Masuk (Sampai)</label>
                            <input type="date" name="date_to" class="form-control"
                                   value="<?= htmlspecialchars($filter_date_to) ?>">
                        </div>

                        <!-- Status Filter -->
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="">Semua Status</option>
                                <?php foreach ($status_labels as $k => $v): ?>
                                <option value="<?= $k ?>" <?= $filter_status_val === $k ? 'selected' : '' ?>>
                                    <?= $v ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Priority Filter -->
                        <div class="mb-4">
                            <label class="form-label">Prioritas</label>
                            <select name="priority" class="form-select">
                                <option value="">Semua Prioritas</option>
                                <?php foreach ($priority_labels as $k => $v): ?>
                                <option value="<?= $k ?>" <?= $filter_priority === $k ? 'selected' : '' ?>>
                                    <?= $v ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-secondary">
                                <i class="fas fa-search me-1"></i>Tampilkan Preview
                            </button>
                            <a href="index.php?page=export" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-1"></i>Reset Filter
                            </a>
                        </div>
                    </form>

                    <!-- Export Filtered CSV -->
                    <?php if (!empty($preview_data)): ?>
                    <div style="margin-top:16px;padding-top:16px;border-top:1px solid #e2e8f0;">
                        <form method="POST">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
                            <input type="hidden" name="action" value="export">
                            <input type="hidden" name="date_from" value="<?= htmlspecialchars($filter_date_from) ?>">
                            <input type="hidden" name="date_to" value="<?= htmlspecialchars($filter_date_to) ?>">
                            <input type="hidden" name="status" value="<?= htmlspecialchars($filter_status_val) ?>">
                            <input type="hidden" name="priority" value="<?= htmlspecialchars($filter_priority) ?>">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-download me-2"></i>
                                Export <?= count($preview_data) ?> Data Terfilter
                            </button>
                        </form>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Export Info Card -->
            <div class="admin-card mt-4">
                <div class="admin-card-body">
                    <div style="text-align:center;">
                        <div class="export-icon">
                            <i class="fas fa-file-csv"></i>
                        </div>
                        <div style="font-weight:700;font-size:14px;color:#1e293b;margin-bottom:8px;">Format CSV</div>
                        <p style="font-size:13px;color:#64748b;margin-bottom:16px;">
                            File CSV dapat dibuka dengan Microsoft Excel, Google Sheets, atau LibreOffice Calc.
                            Kompatibel dengan karakter Indonesia (UTF-8 BOM).
                        </p>
                        <div style="background:#f8fafc;border-radius:8px;padding:12px;font-size:12px;color:#64748b;text-align:left;">
                            <div style="font-weight:700;margin-bottom:8px;color:#374151;">Kolom yang diekspor:</div>
                            <div style="display:grid;grid-template-columns:1fr 1fr;gap:4px;">
                                <div>• ID</div><div>• Nama</div>
                                <div>• Email</div><div>• Telepon</div>
                                <div>• Institusi</div><div>• Layanan</div>
                                <div>• Budget</div><div>• Pesan</div>
                                <div>• Status</div><div>• Prioritas</div>
                                <div>• Catatan</div><div>• Follow Up</div>
                                <div>• Tgl Masuk</div><div>• Tgl Update</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Preview Table -->
        <div class="col-xl-8">
            <div class="admin-card">
                <div class="admin-card-header">
                    <h5 class="admin-card-title">
                        <i class="fas fa-table"></i>
                        Preview Data
                        <span class="badge" style="background:#e2e8f0;color:#475569;font-size:11px;margin-left:4px;">
                            <?= count($preview_data) ?> dari <?= $total_all ?> total
                        </span>
                    </h5>
                    <?php if (!empty($preview_data)): ?>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
                        <input type="hidden" name="action" value="export">
                        <input type="hidden" name="date_from" value="<?= htmlspecialchars($filter_date_from) ?>">
                        <input type="hidden" name="date_to" value="<?= htmlspecialchars($filter_date_to) ?>">
                        <input type="hidden" name="status" value="<?= htmlspecialchars($filter_status_val) ?>">
                        <input type="hidden" name="priority" value="<?= htmlspecialchars($filter_priority) ?>">
                        <button type="submit" class="btn btn-sm btn-primary">
                            <i class="fas fa-download me-1"></i>Export CSV
                        </button>
                    </form>
                    <?php endif; ?>
                </div>
                <div class="admin-card-body">
                    <?php if (!empty($preview_data)): ?>
                    <div class="table-responsive">
                        <table class="table admin-datatable" id="exportTable">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Nama</th>
                                    <th>Email / Telp</th>
                                    <th>Institusi</th>
                                    <th>Layanan</th>
                                    <th>Status</th>
                                    <th>Prioritas</th>
                                    <th>Follow Up</th>
                                    <th>Masuk</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($preview_data as $i => $row): ?>
                                <tr>
                                    <td><?= $row['id'] ?></td>
                                    <td>
                                        <div style="font-weight:600;font-size:13px;"><?= htmlspecialchars($row['name']) ?></div>
                                    </td>
                                    <td>
                                        <div style="font-size:12px;"><?= htmlspecialchars($row['email']) ?></div>
                                        <?php if (!empty($row['phone'])): ?>
                                        <div style="font-size:11px;color:#94a3b8;"><?= htmlspecialchars($row['phone']) ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td style="font-size:12.5px;"><?= htmlspecialchars($row['institution'] ?? '-') ?></td>
                                    <td style="font-size:12.5px;">
                                        <?= htmlspecialchars($service_labels[$row['service_type']] ?? $row['service_type']) ?>
                                    </td>
                                    <td>
                                        <span class="badge badge-status-<?= htmlspecialchars($row['status']) ?>">
                                            <?= htmlspecialchars($status_labels[$row['status']] ?? $row['status']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge badge-priority-<?= htmlspecialchars($row['priority']) ?>">
                                            <?= htmlspecialchars($priority_labels[$row['priority']] ?? $row['priority']) ?>
                                        </span>
                                    </td>
                                    <td style="font-size:12px;color:#64748b;white-space:nowrap;">
                                        <?= !empty($row['follow_up_date']) ? date('d M Y', strtotime($row['follow_up_date'])) : '—' ?>
                                    </td>
                                    <td style="font-size:12px;color:#64748b;white-space:nowrap;">
                                        <?= date('d M Y', strtotime($row['created_at'])) ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-search empty-state-icon"></i>
                        <h4>Tidak Ada Data</h4>
                        <p>
                            <?php if (!empty($filter_date_from) || !empty($filter_date_to) || !empty($filter_status_val) || !empty($filter_priority)): ?>
                                Tidak ada data yang sesuai dengan filter yang dipilih.
                            <?php else: ?>
                                Belum ada data konsultasi yang bisa diekspor.
                            <?php endif; ?>
                        </p>
                        <?php if (!empty($filter_date_from) || !empty($filter_date_to) || !empty($filter_status_val) || !empty($filter_priority)): ?>
                        <a href="index.php?page=export" class="btn btn-outline-secondary">
                            <i class="fas fa-times me-1"></i>Reset Filter
                        </a>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once ADMIN_PATH . '/includes/footer.php'; ?>
