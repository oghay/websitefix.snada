<?php
/**
 * Konsultasi (Consultation) Page
 * Also handles AJAX POST submission.
 */
$siteUrl = defined('SITE_URL') ? SITE_URL : '';
$pageTitle = 'Konsultasi Gratis – ' . getSetting('site_name', 'OJS Developer Indonesia');

// ──────────────────────────────────────────
// HANDLE AJAX / POST SUBMISSION
// ──────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json; charset=utf-8');

    // Verify CSRF
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        echo json_encode(['success' => false, 'message' => 'Token keamanan tidak valid. Silakan muat ulang halaman dan coba lagi.']);
        exit;
    }

    // Validate fields
    $errors = [];
    $name        = sanitize($_POST['name'] ?? '');
    $email       = sanitize($_POST['email'] ?? '');
    $phone       = sanitize($_POST['phone'] ?? '');
    $institution = sanitize($_POST['institution'] ?? '');
    $serviceType = sanitize($_POST['service_type'] ?? 'setup_ojs');
    $budgetRange = sanitize($_POST['budget_range'] ?? '');
    $message     = sanitize($_POST['message'] ?? '');

    $validServices = ['setup_ojs','migrasi','kustomisasi','pelatihan','maintenance','lainnya'];
    $validBudgets  = ['< 3 juta', '3 - 6 juta', '6 - 10 juta', '> 10 juta', 'Belum tahu'];

    if (empty($name) || strlen($name) < 2)      $errors[] = 'Nama lengkap wajib diisi (minimal 2 karakter).';
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Alamat email tidak valid.';
    if (!in_array($serviceType, $validServices)) $errors[] = 'Jenis layanan tidak valid.';
    if (strlen($message) < 10)                  $errors[] = 'Pesan minimal 10 karakter.';

    if (!empty($errors)) {
        echo json_encode(['success' => false, 'message' => implode(' ', $errors)]);
        exit;
    }

    // Insert
    try {
        $id = insert('consultations', [
            'name'         => $name,
            'email'        => $email,
            'phone'        => $phone,
            'institution'  => $institution,
            'service_type' => $serviceType,
            'budget_range' => $budgetRange,
            'message'      => $message,
            'status'       => 'new',
            'priority'     => 'medium',
        ]);

        echo json_encode([
            'success' => true,
            'message' => 'Terima kasih! Pesan konsultasi Anda telah kami terima. Tim kami akan menghubungi Anda dalam 1×24 jam kerja.',
        ]);
    } catch (Exception $e) {
        error_log('Consultation insert error: ' . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan sistem. Silakan coba lagi atau hubungi kami langsung via WhatsApp.']);
    }
    exit;
}

// Pre-fill paket from query string
$prePaket = sanitize($_GET['paket'] ?? '');
$serviceMap = ['basic' => 'setup_ojs', 'starter' => 'setup_ojs', 'professional' => 'kustomisasi', 'premium' => 'maintenance', 'enterprise' => 'maintenance'];
$preService = isset($serviceMap[$prePaket]) ? $serviceMap[$prePaket] : 'setup_ojs';

$waNumber = getSetting('whatsapp_number', '');
?>

<!-- Page Hero -->
<div class="page-hero">
    <div class="container">
        <div class="page-hero-content text-center fade-in-up">
            <h1 class="page-hero-title">Konsultasi Gratis</h1>
            <p class="page-hero-subtitle">Ceritakan kebutuhan jurnal Anda kepada kami. Tanpa biaya, tanpa komitmen — kami siap membantu.</p>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb justify-content-center">
                    <li class="breadcrumb-item"><a href="<?= $siteUrl ?>/">Beranda</a></li>
                    <li class="breadcrumb-item active">Konsultasi</li>
                </ol>
            </nav>
        </div>
    </div>
</div>

<section class="section-padding bg-white">
    <div class="container">
        <div class="row g-5 justify-content-center">

            <!-- Form Column -->
            <div class="col-lg-7">
                <div class="konsultasi-form-card fade-in-up">
                    <div class="konsultasi-form-header">
                        <h3><i class="fas fa-comments me-2"></i>Formulir Konsultasi</h3>
                        <p class="mb-0 opacity-75">Semua kolom bertanda <span class="text-accent fw-bold">*</span> wajib diisi</p>
                    </div>
                    <div class="konsultasi-form-body">

                        <!-- Alert Container (AJAX) -->
                        <div id="formAlert" class="d-none mb-4" role="alert"></div>

                        <!-- Success State -->
                        <div id="formSuccess" class="d-none text-center py-4">
                            <div class="success-icon-wrap mb-3">
                                <i class="fas fa-check-circle fa-4x" style="color: #10b981;"></i>
                            </div>
                            <h4 class="fw-700 mb-2">Pesan Terkirim!</h4>
                            <p class="text-muted">Terima kasih telah menghubungi kami. Tim kami akan membalas dalam <strong>1×24 jam kerja</strong>.</p>
                            <?php if (!empty($waNumber)): ?>
                            <p class="text-muted mb-0">Atau hubungi kami langsung via WhatsApp:</p>
                            <a href="https://wa.me/<?= htmlspecialchars(preg_replace('/\D/', '', $waNumber)) ?>"
                               class="btn btn-accent mt-2" target="_blank" rel="noopener">
                                <i class="fab fa-whatsapp me-2"></i>Chat Sekarang
                            </a>
                            <?php endif; ?>
                        </div>

                        <form id="konsultasiForm" novalidate>
                            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

                            <div class="row g-3">
                                <!-- Nama -->
                                <div class="col-md-6">
                                    <label for="inp_name" class="form-label">
                                        Nama Lengkap <span class="text-accent">*</span>
                                    </label>
                                    <input type="text" id="inp_name" name="name" class="form-control"
                                           placeholder="Dr. Nama Lengkap, M.Pd." required minlength="2">
                                    <div class="invalid-feedback">Nama lengkap wajib diisi.</div>
                                </div>

                                <!-- Email -->
                                <div class="col-md-6">
                                    <label for="inp_email" class="form-label">
                                        Email <span class="text-accent">*</span>
                                    </label>
                                    <input type="email" id="inp_email" name="email" class="form-control"
                                           placeholder="nama@institusi.ac.id" required>
                                    <div class="invalid-feedback">Masukkan alamat email yang valid.</div>
                                </div>

                                <!-- WhatsApp -->
                                <div class="col-md-6">
                                    <label for="inp_phone" class="form-label">
                                        No. WhatsApp
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="fab fa-whatsapp text-success"></i>
                                        </span>
                                        <input type="tel" id="inp_phone" name="phone" class="form-control"
                                               placeholder="08xx-xxxx-xxxx">
                                    </div>
                                    <div class="form-text">Untuk respons lebih cepat via WhatsApp</div>
                                </div>

                                <!-- Institusi -->
                                <div class="col-md-6">
                                    <label for="inp_institution" class="form-label">Institusi / Lembaga</label>
                                    <input type="text" id="inp_institution" name="institution" class="form-control"
                                           placeholder="Nama universitas / lembaga">
                                </div>

                                <!-- Jenis Layanan -->
                                <div class="col-md-6">
                                    <label for="inp_service" class="form-label">
                                        Jenis Layanan <span class="text-accent">*</span>
                                    </label>
                                    <select id="inp_service" name="service_type" class="form-select" required>
                                        <option value="setup_ojs" <?= $preService === 'setup_ojs' ? 'selected' : '' ?>>Setup & Instalasi OJS</option>
                                        <option value="kustomisasi" <?= $preService === 'kustomisasi' ? 'selected' : '' ?>>Kustomisasi Tampilan / Tema</option>
                                        <option value="migrasi">Migrasi Jurnal</option>
                                        <option value="pelatihan">Pelatihan & Workshop OJS</option>
                                        <option value="maintenance" <?= $preService === 'maintenance' ? 'selected' : '' ?>>Maintenance & Technical Support</option>
                                        <option value="lainnya">Lainnya / Saya tidak yakin</option>
                                    </select>
                                </div>

                                <!-- Budget -->
                                <div class="col-md-6">
                                    <label for="inp_budget" class="form-label">Estimasi Anggaran</label>
                                    <select id="inp_budget" name="budget_range" class="form-select">
                                        <option value="">Pilih estimasi anggaran</option>
                                        <option value="< 3 juta">Di bawah Rp 3.000.000</option>
                                        <option value="3 - 6 juta">Rp 3.000.000 – Rp 6.000.000</option>
                                        <option value="6 - 10 juta">Rp 6.000.000 – Rp 10.000.000</option>
                                        <option value="> 10 juta">Di atas Rp 10.000.000</option>
                                        <option value="Belum tahu">Belum tahu / Fleksibel</option>
                                    </select>
                                </div>

                                <!-- Pesan -->
                                <div class="col-12">
                                    <label for="inp_message" class="form-label">
                                        Pesan / Kebutuhan Anda <span class="text-accent">*</span>
                                    </label>
                                    <textarea id="inp_message" name="message" class="form-control"
                                              rows="5" required minlength="10"
                                              placeholder="Ceritakan kondisi jurnal Anda saat ini, apa yang ingin Anda capai, dan pertanyaan yang ingin Anda ajukan..."></textarea>
                                    <div class="invalid-feedback">Pesan wajib diisi (minimal 10 karakter).</div>
                                    <div class="form-text">
                                        Semakin detail informasi yang Anda berikan, semakin tepat rekomendasi yang akan kami berikan.
                                    </div>
                                </div>

                                <!-- Privacy note -->
                                <div class="col-12">
                                    <div class="privacy-note">
                                        <i class="fas fa-shield-alt me-2 text-success"></i>
                                        Informasi Anda akan dijaga kerahasiaannya dan tidak akan disebarkan kepada pihak ketiga manapun.
                                    </div>
                                </div>

                                <!-- Submit -->
                                <div class="col-12">
                                    <button type="submit" id="submitBtn" class="btn btn-accent btn-lg w-100">
                                        <i class="fas fa-paper-plane me-2"></i>Kirim Pesan Konsultasi
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Info Sidebar -->
            <div class="col-lg-5">
                <!-- Contact Info -->
                <div class="konsultasi-info-card fade-in-up">
                    <h5><i class="fas fa-address-book me-2"></i>Cara Lain Menghubungi Kami</h5>
                    <div class="konsultasi-contact-items">
                        <?php if (!empty($waNumber)): ?>
                        <div class="konsultasi-contact-item">
                            <div class="konsultasi-contact-icon whatsapp-icon">
                                <i class="fab fa-whatsapp"></i>
                            </div>
                            <div>
                                <div class="fw-600">WhatsApp</div>
                                <a href="https://wa.me/<?= htmlspecialchars(preg_replace('/\D/', '', $waNumber)) ?>"
                                   target="_blank" rel="noopener"><?= htmlspecialchars($waNumber) ?></a>
                                <div class="text-muted small">Respons cepat, Mon–Fri 08–17 WIB</div>
                            </div>
                        </div>
                        <?php endif; ?>
                        <?php $email = getSetting('email_contact', ''); if ($email): ?>
                        <div class="konsultasi-contact-item">
                            <div class="konsultasi-contact-icon email-icon">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <div>
                                <div class="fw-600">Email</div>
                                <a href="mailto:<?= htmlspecialchars($email) ?>"><?= htmlspecialchars($email) ?></a>
                                <div class="text-muted small">Balasan dalam 1×24 jam kerja</div>
                            </div>
                        </div>
                        <?php else: ?>
                        <div class="konsultasi-contact-item">
                            <div class="konsultasi-contact-icon email-icon">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <div>
                                <div class="fw-600">Email</div>
                                <span>info@ojsdeveloper.id</span>
                                <div class="text-muted small">Balasan dalam 1×24 jam kerja</div>
                            </div>
                        </div>
                        <?php endif; ?>
                        <div class="konsultasi-contact-item">
                            <div class="konsultasi-contact-icon clock-icon">
                                <i class="fas fa-clock"></i>
                            </div>
                            <div>
                                <div class="fw-600">Jam Operasional</div>
                                <span>Senin – Jumat: 08.00 – 17.00 WIB</span>
                                <div class="text-muted small">Sabtu: 09.00 – 13.00 WIB</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Expectations -->
                <div class="konsultasi-expect-card mt-4 fade-in-up" style="animation-delay:0.1s">
                    <h5><i class="fas fa-list-check me-2"></i>Yang Akan Terjadi Selanjutnya</h5>
                    <ol class="konsultasi-expect-list">
                        <li>
                            <span class="expect-num">1</span>
                            <div>
                                <strong>Tim kami akan menghubungi Anda</strong>
                                <p>Dalam 1×24 jam kerja via WhatsApp atau email yang Anda daftarkan.</p>
                            </div>
                        </li>
                        <li>
                            <span class="expect-num">2</span>
                            <div>
                                <strong>Sesi konsultasi gratis</strong>
                                <p>Kami akan mendiskusikan kebutuhan Anda secara mendalam, online atau offline.</p>
                            </div>
                        </li>
                        <li>
                            <span class="expect-num">3</span>
                            <div>
                                <strong>Penawaran tertulis</strong>
                                <p>Anda akan menerima proposal lengkap dengan rincian biaya, scope of work, dan timeline.</p>
                            </div>
                        </li>
                        <li>
                            <span class="expect-num">4</span>
                            <div>
                                <strong>Mulai bekerja</strong>
                                <p>Jika cocok, proyek dimulai sesuai jadwal yang disepakati bersama.</p>
                            </div>
                        </li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
// AJAX Form Submission
document.getElementById('konsultasiForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const form    = this;
    const btn     = document.getElementById('submitBtn');
    const alert   = document.getElementById('formAlert');
    const success = document.getElementById('formSuccess');

    // Client-side validation
    if (!form.checkValidity()) {
        form.classList.add('was-validated');
        return;
    }

    // Loading state
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Mengirim...';
    alert.className = 'd-none';

    const formData = new FormData(form);

    fetch(window.location.href, {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            form.classList.add('d-none');
            success.classList.remove('d-none');
            success.scrollIntoView({ behavior: 'smooth', block: 'start' });
        } else {
            alert.className = 'alert alert-danger mb-4';
            alert.innerHTML = '<i class="fas fa-exclamation-triangle me-2"></i>' + data.message;
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-paper-plane me-2"></i>Kirim Pesan Konsultasi';
        }
    })
    .catch(() => {
        alert.className = 'alert alert-danger mb-4';
        alert.innerHTML = '<i class="fas fa-exclamation-triangle me-2"></i>Terjadi kesalahan jaringan. Silakan coba lagi.';
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-paper-plane me-2"></i>Kirim Pesan Konsultasi';
    });
});
</script>
