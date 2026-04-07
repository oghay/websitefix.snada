<?php
/**
 * Admin Dashboard
 * Overview statistics, charts, and recent activity
 */

// --- Stat Counts ---
$total_portfolio = 0;
$total_blog      = 0;
$new_konsultasi  = 0;
$bulan_ini       = 0;

try {
    $r = fetch("SELECT COUNT(*) as cnt FROM portfolio");
    $total_portfolio = $r ? (int)$r['cnt'] : 0;

    $r = fetch("SELECT COUNT(*) as cnt FROM blog_posts");
    $total_blog = $r ? (int)$r['cnt'] : 0;

    $r = fetch("SELECT COUNT(*) as cnt FROM consultations WHERE status = 'new'");
    $new_konsultasi = $r ? (int)$r['cnt'] : 0;

    $r = fetch("SELECT COUNT(*) as cnt FROM consultations WHERE MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())");
    $bulan_ini = $r ? (int)$r['cnt'] : 0;
} catch (Exception $e) {
    // DB not yet set up
}

// --- Revenue Stats ---
$total_revenue     = 0;
$orders_this_month = 0;
try {
    $r = fetch("SELECT COALESCE(SUM(price), 0) as total FROM orders WHERE status = 'completed'");
    $total_revenue = $r ? (int)$r['total'] : 0;

    $r = fetch("SELECT COUNT(*) as cnt FROM orders WHERE MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())");
    $orders_this_month = $r ? (int)$r['cnt'] : 0;
} catch (Exception $e) {}

// --- Chart Data: Konsultasi per 6 bulan terakhir ---
$chart_months  = [];
$chart_data    = [];
for ($i = 5; $i >= 0; $i--) {
    $ts        = strtotime("-{$i} months");
    $label     = strftime('%b %Y', $ts) ?: date('M Y', $ts);
    $y         = date('Y', $ts);
    $m         = date('m', $ts);
    $chart_months[] = date('M Y', $ts);
    try {
        $r = fetch(
            "SELECT COUNT(*) as cnt FROM consultations WHERE YEAR(created_at) = ? AND MONTH(created_at) = ?",
            [$y, $m]
        );
        $chart_data[] = $r ? (int)$r['cnt'] : 0;
    } catch (Exception $e) {
        $chart_data[] = 0;
    }
}

// --- Chart Data: Status Distribution ---
$status_labels = ['Baru', 'Dihubungi', 'Follow Up', 'Negosiasi', 'Closed Won', 'Closed Lost'];
$status_keys   = ['new', 'contacted', 'follow_up', 'negotiation', 'closed_won', 'closed_lost'];
$status_colors = ['#3b82f6', '#06b6d4', '#f59e0b', '#8b5cf6', '#22c55e', '#ef4444'];
$status_data   = [];
foreach ($status_keys as $sk) {
    try {
        $r = fetch("SELECT COUNT(*) as cnt FROM consultations WHERE status = ?", [$sk]);
        $status_data[] = $r ? (int)$r['cnt'] : 0;
    } catch (Exception $e) {
        $status_data[] = 0;
    }
}

// --- Recent Consultations (latest 5) ---
$recent_konsultasi = [];
try {
    $recent_konsultasi = fetchAll(
        "SELECT id, name, institution, service_type, status, priority, created_at
         FROM consultations ORDER BY created_at DESC LIMIT 5"
    );
} catch (Exception $e) {}

// --- Recent Portfolio (latest 4) ---
$recent_portfolio = [];
try {
    $recent_portfolio = fetchAll(
        "SELECT id, title, client_name, status, image, created_at
         FROM portfolio ORDER BY created_at DESC LIMIT 4"
    );
} catch (Exception $e) {}

// Service type labels
$service_labels = [
    'setup_ojs'    => 'Setup OJS',
    'migrasi'      => 'Migrasi',
    'kustomisasi'  => 'Kustomisasi',
    'pelatihan'    => 'Pelatihan',
    'maintenance'  => 'Maintenance',
    'lainnya'      => 'Lainnya',
];

require_once ADMIN_PATH . '/includes/header.php';
require_once ADMIN_PATH . '/includes/sidebar.php';
?>

<div class="admin-content">
    <!-- Page Header -->
    <div class="page-header">
        <div class="page-header-left">
            <h2>Dashboard</h2>
            <p>Selamat datang kembali, <strong><?= htmlspecialchars($_SESSION['admin_name'] ?? 'Admin') ?></strong>. Berikut ringkasan hari ini.</p>
        </div>
        <div class="page-header-actions">
            <span class="badge" style="background:#e2e8f0;color:#475569;font-size:12px;padding:8px 14px;">
                <i class="fas fa-calendar-day me-1"></i>
                <?= date('d F Y') ?>
            </span>
        </div>
    </div>

    <!-- Revenue Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-6">
            <div style="background:linear-gradient(135deg,#065f46,#059669);border-radius:16px;padding:24px 28px;display:flex;align-items:center;gap:20px;box-shadow:0 4px 24px rgba(5,150,105,0.2);">
                <div style="background:rgba(255,255,255,0.15);border-radius:14px;width:60px;height:60px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <i class="fas fa-coins fa-xl" style="color:#fff;"></i>
                </div>
                <div>
                    <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.1em;color:rgba(255,255,255,0.7);margin-bottom:4px;">Total Pendapatan</div>
                    <div style="font-size:26px;font-weight:800;color:#fff;line-height:1.1;">Rp <?= number_format($total_revenue, 0, ',', '.') ?></div>
                    <div style="font-size:12px;color:rgba(255,255,255,0.65);margin-top:4px;"><i class="fas fa-check-circle me-1"></i>Dari pesanan yang selesai</div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div style="background:linear-gradient(135deg,#1e3a5f,#2563eb);border-radius:16px;padding:24px 28px;display:flex;align-items:center;gap:20px;box-shadow:0 4px 24px rgba(37,99,235,0.2);">
                <div style="background:rgba(255,255,255,0.15);border-radius:14px;width:60px;height:60px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <i class="fas fa-shopping-bag fa-xl" style="color:#fff;"></i>
                </div>
                <div>
                    <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.1em;color:rgba(255,255,255,0.7);margin-bottom:4px;">Pesanan Bulan Ini</div>
                    <div style="font-size:26px;font-weight:800;color:#fff;line-height:1.1;"><?= $orders_this_month ?> Pesanan</div>
                    <div style="font-size:12px;color:rgba(255,255,255,0.65);margin-top:4px;"><a href="index.php?page=pesanan" style="color:rgba(255,255,255,0.8);text-decoration:none;">Lihat semua pesanan →</a></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Stat Cards -->
    <div class="row g-4 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="stat-card stat-card-primary">
                <div class="stat-icon">
                    <i class="fas fa-briefcase"></i>
                </div>
                <div class="stat-info">
                    <div class="stat-value"><?= $total_portfolio ?></div>
                    <div class="stat-label">Total Portofolio</div>
                    <div class="stat-change up">
                        <i class="fas fa-arrow-up"></i>
                        <a href="index.php?page=portofolio" style="color:inherit;font-size:11px;text-decoration:none;">Kelola portofolio →</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="stat-card stat-card-success">
                <div class="stat-icon">
                    <i class="fas fa-newspaper"></i>
                </div>
                <div class="stat-info">
                    <div class="stat-value"><?= $total_blog ?></div>
                    <div class="stat-label">Total Artikel Blog</div>
                    <div class="stat-change up">
                        <i class="fas fa-arrow-up"></i>
                        <a href="index.php?page=blog" style="color:inherit;font-size:11px;text-decoration:none;">Kelola blog →</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="stat-card stat-card-warning">
                <div class="stat-icon">
                    <i class="fas fa-bell"></i>
                </div>
                <div class="stat-info">
                    <div class="stat-value"><?= $new_konsultasi ?></div>
                    <div class="stat-label">Konsultasi Baru</div>
                    <div class="stat-change <?= $new_konsultasi > 0 ? 'up' : '' ?>">
                        <?php if ($new_konsultasi > 0): ?>
                            <i class="fas fa-exclamation-circle"></i>
                            <span style="font-size:11px;">Perlu ditindaklanjuti</span>
                        <?php else: ?>
                            <span style="font-size:11px;">Tidak ada yang baru</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="stat-card stat-card-info">
                <div class="stat-icon">
                    <i class="fas fa-comments"></i>
                </div>
                <div class="stat-info">
                    <div class="stat-value"><?= $bulan_ini ?></div>
                    <div class="stat-label">Konsultasi Bulan Ini</div>
                    <div class="stat-change">
                        <i class="fas fa-calendar"></i>
                        <span style="font-size:11px;"><?= date('F Y') ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row g-4 mb-4">
        <!-- Bar Chart: Konsultasi per bulan -->
        <div class="col-xl-7">
            <div class="admin-card h-100">
                <div class="admin-card-header">
                    <h5 class="admin-card-title">
                        <i class="fas fa-chart-bar"></i>
                        Konsultasi 6 Bulan Terakhir
                    </h5>
                </div>
                <div class="admin-card-body">
                    <div class="chart-container" style="height: 280px;">
                        <canvas id="konsultasiChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Doughnut Chart: Status Distribution -->
        <div class="col-xl-5">
            <div class="admin-card h-100">
                <div class="admin-card-header">
                    <h5 class="admin-card-title">
                        <i class="fas fa-chart-pie"></i>
                        Distribusi Status Konsultasi
                    </h5>
                </div>
                <div class="admin-card-body">
                    <?php $total_all = array_sum($status_data); ?>
                    <?php if ($total_all > 0): ?>
                    <div class="chart-container" style="height: 220px;">
                        <canvas id="statusChart"></canvas>
                    </div>
                    <!-- Legend -->
                    <div class="mt-3" style="display:grid;grid-template-columns:1fr 1fr;gap:6px;">
                        <?php foreach ($status_labels as $i => $lbl): ?>
                        <div style="display:flex;align-items:center;gap:6px;font-size:12px;">
                            <span style="width:10px;height:10px;border-radius:50%;background:<?= $status_colors[$i] ?>;flex-shrink:0;"></span>
                            <span><?= $lbl ?>: <strong><?= $status_data[$i] ?></strong></span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php else: ?>
                    <div class="empty-state" style="padding:40px 0;">
                        <i class="fas fa-chart-pie empty-state-icon"></i>
                        <p>Belum ada data konsultasi</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Consultations + Quick Actions -->
    <div class="row g-4 mb-4">
        <!-- Recent Consultations Table -->
        <div class="col-xl-8">
            <div class="admin-card">
                <div class="admin-card-header">
                    <h5 class="admin-card-title">
                        <i class="fas fa-clock"></i>
                        Konsultasi Terbaru
                    </h5>
                    <a href="index.php?page=konsultasi" class="btn btn-sm btn-outline-secondary">
                        Lihat Semua <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>
                <div class="admin-card-body p-0">
                    <?php if (!empty($recent_konsultasi)): ?>
                    <div class="table-responsive">
                        <table class="table mb-0">
                            <thead>
                                <tr>
                                    <th>Nama</th>
                                    <th>Layanan</th>
                                    <th>Status</th>
                                    <th>Tanggal</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_konsultasi as $k): ?>
                                <tr>
                                    <td>
                                        <div style="font-weight:600;"><?= htmlspecialchars($k['name']) ?></div>
                                        <div style="font-size:12px;color:#64748b;"><?= htmlspecialchars($k['institution'] ?? '-') ?></div>
                                    </td>
                                    <td>
                                        <span style="font-size:12.5px;">
                                            <?= htmlspecialchars($service_labels[$k['service_type']] ?? $k['service_type']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge badge-status-<?= htmlspecialchars($k['status']) ?>">
                                            <?= function_exists('getStatusLabel') ? getStatusLabel($k['status']) : htmlspecialchars($k['status']) ?>
                                        </span>
                                    </td>
                                    <td style="font-size:12px;color:#64748b;white-space:nowrap;">
                                        <?= function_exists('formatDate') ? formatDate($k['created_at']) : date('d M Y', strtotime($k['created_at'])) ?>
                                    </td>
                                    <td>
                                        <a href="index.php?page=konsultasi-detail&id=<?= $k['id'] ?>"
                                           class="btn btn-xs btn-outline-primary">
                                            Detail
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-inbox empty-state-icon"></i>
                        <h4>Belum Ada Konsultasi</h4>
                        <p>Konsultasi dari calon klien akan muncul di sini.</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="col-xl-4">
            <div class="admin-card">
                <div class="admin-card-header">
                    <h5 class="admin-card-title">
                        <i class="fas fa-bolt"></i>
                        Aksi Cepat
                    </h5>
                </div>
                <div class="admin-card-body">
                    <div style="display:flex;flex-direction:column;gap:10px;">
                        <a href="index.php?page=portofolio-form" class="quick-action-btn">
                            <div class="quick-action-icon" style="background:rgba(26,54,93,0.1);color:#1a365d;">
                                <i class="fas fa-plus"></i>
                            </div>
                            <div>
                                <div style="font-weight:700;font-size:13.5px;">Tambah Portofolio</div>
                                <div style="font-size:12px;color:#64748b;">Unggah proyek baru</div>
                            </div>
                        </a>

                        <a href="index.php?page=blog-form" class="quick-action-btn">
                            <div class="quick-action-icon" style="background:rgba(22,163,74,0.1);color:#16a34a;">
                                <i class="fas fa-pen"></i>
                            </div>
                            <div>
                                <div style="font-weight:700;font-size:13.5px;">Tulis Artikel Blog</div>
                                <div style="font-size:12px;color:#64748b;">Buat konten baru</div>
                            </div>
                        </a>

                        <a href="index.php?page=konsultasi" class="quick-action-btn">
                            <div class="quick-action-icon">
                                <i class="fas fa-comments"></i>
                            </div>
                            <div>
                                <div style="font-weight:700;font-size:13.5px;">Lihat Konsultasi</div>
                                <div style="font-size:12px;color:#64748b;">
                                    <?php if ($new_konsultasi > 0): ?>
                                        <span style="color:#d97706;"><?= $new_konsultasi ?> baru perlu ditindak</span>
                                    <?php else: ?>
                                        Kelola CRM konsultasi
                                    <?php endif; ?>
                                </div>
                            </div>
                        </a>

                        <a href="index.php?page=export" class="quick-action-btn">
                            <div class="quick-action-icon" style="background:rgba(217,119,6,0.1);color:#d97706;">
                                <i class="fas fa-download"></i>
                            </div>
                            <div>
                                <div style="font-weight:700;font-size:13.5px;">Export Data CSV</div>
                                <div style="font-size:12px;color:#64748b;">Unduh laporan konsultasi</div>
                            </div>
                        </a>

                        <a href="index.php?page=pengaturan" class="quick-action-btn">
                            <div class="quick-action-icon" style="background:rgba(100,116,139,0.1);color:#475569;">
                                <i class="fas fa-cog"></i>
                            </div>
                            <div>
                                <div style="font-weight:700;font-size:13.5px;">Pengaturan Situs</div>
                                <div style="font-size:12px;color:#64748b;">Konfigurasi website</div>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$extra_js = '
<script>
document.addEventListener("DOMContentLoaded", function() {
    // --- Bar Chart: Konsultasi per Bulan ---
    const barCtx = document.getElementById("konsultasiChart");
    if (barCtx) {
        new Chart(barCtx, {
            type: "bar",
            data: {
                labels: ' . json_encode($chart_months) . ',
                datasets: [{
                    label: "Konsultasi",
                    data: ' . json_encode($chart_data) . ',
                    backgroundColor: "rgba(13, 148, 136, 0.15)",
                    borderColor: "#0d9488",
                    borderWidth: 2,
                    borderRadius: 8,
                    borderSkipped: false,
                    hoverBackgroundColor: "rgba(13, 148, 136, 0.3)",
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            title: function(items) { return items[0].label; },
                            label: function(item) { return item.raw + " konsultasi"; }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { font: { family: "Plus Jakarta Sans", size: 12 } }
                    },
                    y: {
                        beginAtZero: true,
                        ticks: {
                            font: { family: "Plus Jakarta Sans", size: 12 },
                            stepSize: 1,
                            precision: 0
                        },
                        grid: { color: "#f1f5f9" }
                    }
                }
            }
        });
    }

    // --- Doughnut Chart: Status Distribution ---
    const doughCtx = document.getElementById("statusChart");
    if (doughCtx && ' . array_sum($status_data) . ' > 0) {
        new Chart(doughCtx, {
            type: "doughnut",
            data: {
                labels: ' . json_encode($status_labels) . ',
                datasets: [{
                    data: ' . json_encode($status_data) . ',
                    backgroundColor: ' . json_encode($status_colors) . ',
                    borderWidth: 0,
                    hoverOffset: 8,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: "68%",
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(item) {
                                const total = item.dataset.data.reduce((a,b) => a+b, 0);
                                const pct = total > 0 ? Math.round(item.raw / total * 100) : 0;
                                return item.label + ": " + item.raw + " (" + pct + "%)";
                            }
                        }
                    }
                }
            }
        });
    }
});
</script>
';
require_once ADMIN_PATH . '/includes/footer.php';
?>
