<?php
/**
 * Tracking Pesanan Page
 * Frontend: form input kode tracking → AJAX lookup → timeline progres.
 * AJAX POST handler returns JSON.
 */
$siteUrl   = defined('SITE_URL') ? SITE_URL : '';
$pageTitle = 'Tracking Pesanan – ' . getSetting('site_name', 'OJS Developer Indonesia');

// ──────────────────────────────────────────
// HANDLE AJAX POST — lookup tracking code
// ──────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json; charset=utf-8');

    $trackingCode = trim(sanitize($_POST['tracking_code'] ?? ''));

    if (empty($trackingCode)) {
        echo json_encode(['success' => false, 'message' => 'Masukkan kode tracking pesanan Anda.']);
        exit;
    }

    // Validate format: SNADA-ddMMyyyy-NNN
    if (!preg_match('/^SNADA-\d{8}-\d{3,}$/i', $trackingCode)) {
        echo json_encode(['success' => false, 'message' => 'Format kode tracking tidak valid. Contoh: SNADA-06042026-001']);
        exit;
    }

    // Lookup order
    try {
        $order = fetch(
            "SELECT id, tracking_code, client_name, client_institution, service_type,
                    package_tier, status, created_at, updated_at
             FROM orders WHERE tracking_code = ?",
            [strtoupper($trackingCode)]
        );
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan server. Silakan coba lagi.']);
        exit;
    }

    if (!$order) {
        echo json_encode(['success' => false, 'message' => 'Kode tracking tidak ditemukan. Pastikan kode yang Anda masukkan sudah benar.']);
        exit;
    }

    // Get milestones
    $milestones = [];
    try {
        $milestones = fetchAll(
            "SELECT id, title, description, status, completed_at, sort_order
             FROM order_milestones
             WHERE order_id = ?
             ORDER BY sort_order ASC, id ASC",
            [$order['id']]
        );
    } catch (Exception $e) {}

    // Calculate progress
    $totalMs     = count($milestones);
    $completedMs = 0;
    foreach ($milestones as $ms) {
        if ($ms['status'] === 'completed') $completedMs++;
    }
    $progressPct = $totalMs > 0 ? round(($completedMs / $totalMs) * 100) : 0;

    // Service type label
    $serviceLabels = [
        'setup_ojs'   => 'Setup OJS Baru',
        'migrasi'     => 'Migrasi OJS',
        'kustomisasi' => 'Kustomisasi OJS',
        'pelatihan'   => 'Pelatihan',
        'maintenance' => 'Maintenance',
        'lainnya'     => 'Lainnya',
    ];
    $serviceLabel = $serviceLabels[$order['service_type']] ?? ucfirst($order['service_type']);

    // Package tier label
    $tierLabels = [
        'basic'        => 'Basic',
        'professional' => 'Professional',
        'premium'      => 'Premium',
    ];
    $tierLabel = $tierLabels[$order['package_tier']] ?? ($order['package_tier'] ?: '-');

    echo json_encode([
        'success' => true,
        'order'   => [
            'tracking_code'  => $order['tracking_code'],
            'client_name'    => $order['client_name'],
            'institution'    => $order['client_institution'] ?: '-',
            'service_type'   => $serviceLabel,
            'package_tier'   => $tierLabel,
            'status'         => $order['status'],
            'status_label'   => getOrderStatusLabel($order['status']),
            'status_badge'   => getOrderStatusBadge($order['status']),
            'created_at'     => date('d M Y', strtotime($order['created_at'])),
            'updated_at'     => date('d M Y, H:i', strtotime($order['updated_at'])),
            'progress'       => $progressPct,
            'completed_count'=> $completedMs,
            'total_count'    => $totalMs,
        ],
        'milestones' => array_map(function($ms) {
            return [
                'title'        => $ms['title'],
                'description'  => $ms['description'],
                'status'       => $ms['status'],
                'status_label' => getMilestoneStatusLabel($ms['status']),
                'completed_at' => $ms['completed_at'] ? date('d M Y, H:i', strtotime($ms['completed_at'])) : null,
            ];
        }, $milestones),
    ]);
    exit;
}
?>

<!-- Page Hero -->
<div class="page-hero">
    <div class="container">
        <div class="page-hero-content text-center fade-in-up">
            <h1 class="page-hero-title">Tracking Pesanan</h1>
            <p class="page-hero-subtitle">Pantau progres pengerjaan pesanan Anda secara real-time</p>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb justify-content-center">
                    <li class="breadcrumb-item"><a href="<?= $siteUrl ?>/">Beranda</a></li>
                    <li class="breadcrumb-item active">Tracking Pesanan</li>
                </ol>
            </nav>
        </div>
    </div>
</div>

<!-- ══════════════════════════════════════════
     TRACKING SEARCH FORM
══════════════════════════════════════════ -->
<section class="section-padding bg-white">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-7 col-xl-6">
                <div class="tracking-search-card fade-in-up">
                    <div class="tracking-search-icon">
                        <i class="fas fa-shipping-fast"></i>
                    </div>
                    <h2 class="tracking-search-title">Cek Progres Pesanan</h2>
                    <p class="text-muted mb-4">Masukkan kode tracking yang Anda terima saat pemesanan untuk melihat status dan progres pengerjaan.</p>

                    <form id="trackingForm" autocomplete="off">
                        <div class="tracking-input-group">
                            <div class="tracking-input-wrapper">
                                <i class="fas fa-hashtag tracking-input-icon"></i>
                                <input type="text"
                                       id="trackingCode"
                                       class="form-control tracking-input"
                                       placeholder="Contoh: SNADA-06042026-001"
                                       maxlength="25"
                                       required
                                       style="text-transform:uppercase;">
                            </div>
                            <button type="submit" class="btn btn-primary tracking-btn" id="trackingBtn">
                                <span class="tracking-btn-text">
                                    <i class="fas fa-search me-2"></i>Cek Progres
                                </span>
                                <span class="tracking-btn-loading d-none">
                                    <span class="spinner-border spinner-border-sm me-2"></span>Mencari...
                                </span>
                            </button>
                        </div>
                    </form>

                    <!-- Error message -->
                    <div id="trackingError" class="tracking-error d-none">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        <span id="trackingErrorMsg"></span>
                    </div>

                    <div class="tracking-hint">
                        <i class="fas fa-info-circle me-1"></i>
                        Kode tracking dikirim melalui email saat pesanan dikonfirmasi.
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ══════════════════════════════════════════
     TRACKING RESULT (hidden until search)
══════════════════════════════════════════ -->
<section id="trackingResult" class="section-padding bg-light d-none">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 col-xl-7">

                <!-- Order Summary Card -->
                <div class="tracking-summary fade-in-up">
                    <div class="tracking-summary-header">
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                            <div>
                                <span class="tracking-code-label">Kode Tracking</span>
                                <h3 class="tracking-code-value" id="resTrackingCode"></h3>
                            </div>
                            <span class="badge tracking-status-badge" id="resStatusBadge"></span>
                        </div>
                    </div>

                    <div class="tracking-summary-body">
                        <div class="row g-3">
                            <div class="col-sm-6">
                                <div class="tracking-info-item">
                                    <span class="tracking-info-label"><i class="fas fa-user me-1"></i> Klien</span>
                                    <span class="tracking-info-value" id="resClientName"></span>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="tracking-info-item">
                                    <span class="tracking-info-label"><i class="fas fa-university me-1"></i> Institusi</span>
                                    <span class="tracking-info-value" id="resInstitution"></span>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="tracking-info-item">
                                    <span class="tracking-info-label"><i class="fas fa-cog me-1"></i> Layanan</span>
                                    <span class="tracking-info-value" id="resService"></span>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="tracking-info-item">
                                    <span class="tracking-info-label"><i class="fas fa-box me-1"></i> Paket</span>
                                    <span class="tracking-info-value" id="resTier"></span>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="tracking-info-item">
                                    <span class="tracking-info-label"><i class="fas fa-calendar-alt me-1"></i> Tanggal Pesanan</span>
                                    <span class="tracking-info-value" id="resCreated"></span>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="tracking-info-item">
                                    <span class="tracking-info-label"><i class="fas fa-sync-alt me-1"></i> Terakhir Update</span>
                                    <span class="tracking-info-value" id="resUpdated"></span>
                                </div>
                            </div>
                        </div>

                        <!-- Progress Bar -->
                        <div class="tracking-progress-section">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="tracking-progress-label">Progres Pengerjaan</span>
                                <span class="tracking-progress-pct" id="resProgressPct"></span>
                            </div>
                            <div class="progress tracking-progress-bar">
                                <div class="progress-bar" id="resProgressBar" role="progressbar"
                                     style="width:0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                            <div class="tracking-progress-count" id="resProgressCount"></div>
                        </div>
                    </div>
                </div>

                <!-- Milestone Timeline -->
                <div class="tracking-timeline-section fade-in-up">
                    <h4 class="tracking-timeline-title">
                        <i class="fas fa-tasks me-2"></i>Detail Progres
                    </h4>
                    <div class="tracking-timeline" id="trackingTimeline">
                        <!-- Milestones rendered by JS -->
                    </div>
                </div>

            </div>
        </div>
    </div>
</section>

<!-- ══════════════════════════════════════════
     INFO SECTION
══════════════════════════════════════════ -->
<section class="section-padding bg-white">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="text-center fade-in-up">
                    <span class="section-badge">Cara Kerja</span>
                    <h2 class="section-title">Bagaimana Tracking Pesanan Bekerja?</h2>
                </div>
                <div class="row g-4 mt-3">
                    <div class="col-md-4 fade-in-up">
                        <div class="tracking-howto-card">
                            <div class="tracking-howto-num">1</div>
                            <h5>Pesan Layanan</h5>
                            <p class="text-muted small mb-0">Konsultasikan kebutuhan Anda, pilih paket, dan lakukan pemesanan.</p>
                        </div>
                    </div>
                    <div class="col-md-4 fade-in-up">
                        <div class="tracking-howto-card">
                            <div class="tracking-howto-num">2</div>
                            <h5>Terima Kode</h5>
                            <p class="text-muted small mb-0">Kode tracking unik dikirim via email untuk memantau progres Anda.</p>
                        </div>
                    </div>
                    <div class="col-md-4 fade-in-up">
                        <div class="tracking-howto-card">
                            <div class="tracking-howto-num">3</div>
                            <h5>Pantau Progres</h5>
                            <p class="text-muted small mb-0">Masukkan kode di halaman ini untuk melihat status real-time pengerjaan.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="cta-section">
    <div class="container text-center">
        <div class="fade-in-up">
            <h2 class="cta-title" style="color:#fff;">Belum Memiliki Kode Tracking?</h2>
            <p class="cta-subtitle" style="color:rgba(255,255,255,0.85);">Mulai konsultasi gratis untuk mendapatkan layanan OJS terbaik bagi institusi Anda.</p>
            <div class="cta-actions justify-content-center">
                <a href="<?= $siteUrl ?>/konsultasi" class="btn btn-accent btn-lg">
                    <i class="fas fa-comments me-2"></i>Konsultasi Gratis
                </a>
                <a href="<?= $siteUrl ?>/harga" class="btn btn-outline-light btn-lg">
                    <i class="fas fa-tags me-2"></i>Lihat Paket Harga
                </a>
            </div>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form       = document.getElementById('trackingForm');
    const codeInput  = document.getElementById('trackingCode');
    const btn        = document.getElementById('trackingBtn');
    const btnText    = btn.querySelector('.tracking-btn-text');
    const btnLoad    = btn.querySelector('.tracking-btn-loading');
    const errorBox   = document.getElementById('trackingError');
    const errorMsg   = document.getElementById('trackingErrorMsg');
    const resultSec  = document.getElementById('trackingResult');

    // Auto-uppercase input
    codeInput.addEventListener('input', function() {
        this.value = this.value.toUpperCase();
    });

    // Check URL params on load (for shared links)
    const urlParams = new URLSearchParams(window.location.search);
    const preCode   = urlParams.get('code') || urlParams.get('kode');
    if (preCode) {
        codeInput.value = preCode.toUpperCase();
        form.dispatchEvent(new Event('submit'));
    }

    form.addEventListener('submit', function(e) {
        e.preventDefault();

        const code = codeInput.value.trim();
        if (!code) {
            showError('Masukkan kode tracking pesanan Anda.');
            return;
        }

        // UI: loading state
        btnText.classList.add('d-none');
        btnLoad.classList.remove('d-none');
        btn.disabled = true;
        hideError();
        resultSec.classList.add('d-none');

        const formData = new FormData();
        formData.append('tracking_code', code);

        fetch(window.location.pathname, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: formData
        })
        .then(r => r.json())
        .then(data => {
            btnText.classList.remove('d-none');
            btnLoad.classList.add('d-none');
            btn.disabled = false;

            if (!data.success) {
                showError(data.message);
                return;
            }

            renderResult(data.order, data.milestones);
        })
        .catch(() => {
            btnText.classList.remove('d-none');
            btnLoad.classList.add('d-none');
            btn.disabled = false;
            showError('Gagal menghubungi server. Periksa koneksi internet Anda.');
        });
    });

    function showError(msg) {
        errorMsg.textContent = msg;
        errorBox.classList.remove('d-none');
    }

    function hideError() {
        errorBox.classList.add('d-none');
    }

    function renderResult(order, milestones) {
        // Order summary
        document.getElementById('resTrackingCode').textContent = order.tracking_code;
        document.getElementById('resClientName').textContent   = order.client_name;
        document.getElementById('resInstitution').textContent  = order.institution;
        document.getElementById('resService').textContent      = order.service_type;
        document.getElementById('resTier').textContent         = order.package_tier;
        document.getElementById('resCreated').textContent      = order.created_at;
        document.getElementById('resUpdated').textContent      = order.updated_at;

        // Status badge
        const badge = document.getElementById('resStatusBadge');
        badge.textContent = order.status_label;
        badge.className   = 'badge tracking-status-badge bg-' + order.status_badge;

        // Progress bar
        const pct = order.progress;
        document.getElementById('resProgressPct').textContent = pct + '%';
        const bar = document.getElementById('resProgressBar');
        bar.style.width = '0%';
        bar.setAttribute('aria-valuenow', pct);

        // Color based on progress
        let barClass = 'bg-info';
        if (pct >= 100) barClass = 'bg-success';
        else if (pct >= 60) barClass = 'bg-primary';
        bar.className = 'progress-bar progress-bar-striped progress-bar-animated ' + barClass;

        // Animate progress bar
        setTimeout(() => { bar.style.width = pct + '%'; }, 100);

        document.getElementById('resProgressCount').textContent =
            order.completed_count + ' dari ' + order.total_count + ' tahapan selesai';

        // Milestones timeline
        const timeline = document.getElementById('trackingTimeline');
        timeline.innerHTML = '';

        milestones.forEach(function(ms, idx) {
            const item = document.createElement('div');
            item.className = 'tracking-tl-item tracking-tl-' + ms.status;

            let statusIcon = '';
            let statusClass = '';
            if (ms.status === 'completed') {
                statusIcon  = '<i class="fas fa-check"></i>';
                statusClass = 'tracking-tl-dot-completed';
            } else if (ms.status === 'in_progress') {
                statusIcon  = '<i class="fas fa-spinner fa-spin"></i>';
                statusClass = 'tracking-tl-dot-progress';
            } else {
                statusIcon  = '<span>' + (idx + 1) + '</span>';
                statusClass = 'tracking-tl-dot-pending';
            }

            let completedHtml = '';
            if (ms.completed_at) {
                completedHtml = '<span class="tracking-tl-date"><i class="fas fa-check-circle me-1"></i>Selesai: ' + ms.completed_at + '</span>';
            } else if (ms.status === 'in_progress') {
                completedHtml = '<span class="tracking-tl-date tracking-tl-date-active"><i class="fas fa-clock me-1"></i>Sedang dikerjakan</span>';
            }

            item.innerHTML =
                '<div class="tracking-tl-dot ' + statusClass + '">' + statusIcon + '</div>' +
                '<div class="tracking-tl-content">' +
                    '<h6 class="tracking-tl-title">' + escapeHtml(ms.title) + '</h6>' +
                    '<p class="tracking-tl-desc">' + escapeHtml(ms.description) + '</p>' +
                    completedHtml +
                '</div>';

            timeline.appendChild(item);
        });

        // Show result section with smooth scroll
        resultSec.classList.remove('d-none');
        setTimeout(() => {
            resultSec.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }, 150);
    }

    function escapeHtml(str) {
        if (!str) return '';
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }
});
</script>
