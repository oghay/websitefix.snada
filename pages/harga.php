<?php
/**
 * Harga (Pricing) Page
 * Adapted from Snada Studio reference – no fixed prices, "Hubungi Kami" approach
 */
$pageTitle = 'Paket & Harga – ' . getSetting('site_name', 'OJS Developer Indonesia');
$metaDesc  = 'Paket harga setup OJS, indeksasi, migrasi, dan layanan tambahan jurnal akademik yang transparan dan kompetitif.';
$siteUrl   = defined('SITE_URL') ? SITE_URL : '';
?>

<!-- Page Hero -->
<div class="page-hero">
    <div class="container">
        <div class="page-hero-content text-center fade-in-up">
            <h1 class="page-hero-title">Paket & Harga Layanan</h1>
            <p class="page-hero-subtitle">Transparansi harga dengan paket dan estimasi biaya yang jelas. Pilih paket yang sesuai dengan kebutuhan dan anggaran institusi Anda.</p>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb justify-content-center">
                    <li class="breadcrumb-item"><a href="<?= $siteUrl ?>/">Beranda</a></li>
                    <li class="breadcrumb-item active">Harga</li>
                </ol>
            </nav>
        </div>
    </div>
</div>

<!-- ══════════════════════════════════════════
     PRICING CARDS – 3 Tiers (No Fixed Prices)
══════════════════════════════════════════ -->
<section class="section-padding bg-white">
    <div class="container">
        <div class="row g-4 align-items-stretch justify-content-center">

            <!-- BASIC -->
            <div class="col-md-6 col-lg-4">
                <div class="pricing-card fade-in-up">
                    <div class="pricing-card-header">
                        <div class="pricing-tier-icon">
                            <i class="fas fa-seedling"></i>
                        </div>
                        <h3 class="pricing-tier-name">Basic</h3>
                        <p class="pricing-tier-desc">Cocok untuk jurnal baru yang ingin memulai dengan setup standar OJS.</p>
                        <div class="pricing-price">
                            <span class="pricing-amount" style="font-size:1.5rem;">Hubungi Kami</span>
                        </div>
                        <p class="pricing-period text-muted">Harga disesuaikan kebutuhan</p>
                    </div>
                    <div class="pricing-card-body">
                        <ul class="pricing-feature-list">
                            <li><i class="fas fa-check-circle text-success"></i> Instalasi OJS 3.x terbaru</li>
                            <li><i class="fas fa-check-circle text-success"></i> Konfigurasi server & database</li>
                            <li><i class="fas fa-check-circle text-success"></i> SSL certificate setup</li>
                            <li><i class="fas fa-check-circle text-success"></i> Konfigurasi email SMTP</li>
                            <li><i class="fas fa-check-circle text-success"></i> Theme default OJS</li>
                            <li><i class="fas fa-check-circle text-success"></i> Setup tim editorial dasar</li>
                            <li><i class="fas fa-check-circle text-success"></i> Training dasar (2 jam)</li>
                            <li><i class="fas fa-check-circle text-success"></i> Support 30 hari</li>
                            <li class="text-muted"><i class="fas fa-times-circle"></i> Custom theme design</li>
                            <li class="text-muted"><i class="fas fa-times-circle"></i> SEO & Google Scholar</li>
                            <li class="text-muted"><i class="fas fa-times-circle"></i> Integrasi DOI</li>
                        </ul>
                        <a href="<?= $siteUrl ?>/konsultasi?paket=basic" class="btn btn-outline-primary w-100 mt-3">
                            <i class="fas fa-comments me-1"></i> Konsultasi Paket Basic
                        </a>
                    </div>
                </div>
            </div>

            <!-- PROFESSIONAL (Popular) -->
            <div class="col-md-6 col-lg-4">
                <div class="pricing-card pricing-card-popular fade-in-up" style="animation-delay:0.1s">
                    <div class="pricing-popular-badge">
                        <i class="fas fa-star me-1"></i> Paling Populer
                    </div>
                    <div class="pricing-card-header">
                        <div class="pricing-tier-icon">
                            <i class="fas fa-rocket"></i>
                        </div>
                        <h3 class="pricing-tier-name">Professional</h3>
                        <p class="pricing-tier-desc">Untuk jurnal yang ingin tampilan profesional dengan custom theme dan optimasi SEO.</p>
                        <div class="pricing-price">
                            <span class="pricing-amount" style="font-size:1.5rem;">Hubungi Kami</span>
                        </div>
                        <p class="pricing-period" style="opacity:0.8;">Harga disesuaikan kebutuhan</p>
                    </div>
                    <div class="pricing-card-body">
                        <ul class="pricing-feature-list">
                            <li><i class="fas fa-check-circle"></i> Semua fitur paket Basic</li>
                            <li><i class="fas fa-check-circle"></i> Custom theme design kustom</li>
                            <li><i class="fas fa-check-circle"></i> Responsive & mobile-friendly</li>
                            <li><i class="fas fa-check-circle"></i> Branding sesuai identitas institusi</li>
                            <li><i class="fas fa-check-circle"></i> SEO optimization</li>
                            <li><i class="fas fa-check-circle"></i> Google Scholar indexing</li>
                            <li><i class="fas fa-check-circle"></i> Integrasi Crossref DOI</li>
                            <li><i class="fas fa-check-circle"></i> Training lengkap (4 jam)</li>
                            <li><i class="fas fa-check-circle"></i> Support 60 hari</li>
                            <li class="text-muted" style="opacity:0.7;"><i class="fas fa-times-circle"></i> DOAJ submission support</li>
                            <li class="text-muted" style="opacity:0.7;"><i class="fas fa-times-circle"></i> Maintenance berkala</li>
                        </ul>
                        <a href="<?= $siteUrl ?>/konsultasi?paket=professional" class="btn btn-accent w-100 mt-3">
                            <i class="fas fa-rocket me-1"></i> Konsultasi Paket Professional
                        </a>
                    </div>
                </div>
            </div>

            <!-- PREMIUM -->
            <div class="col-md-6 col-lg-4">
                <div class="pricing-card fade-in-up" style="animation-delay:0.2s">
                    <div class="pricing-card-header">
                        <div class="pricing-tier-icon">
                            <i class="fas fa-crown"></i>
                        </div>
                        <h3 class="pricing-tier-name">Premium</h3>
                        <p class="pricing-tier-desc">Full service termasuk pendampingan indeksasi DOAJ dan maintenance berkala.</p>
                        <div class="pricing-price">
                            <span class="pricing-amount" style="font-size:1.5rem;">Hubungi Kami</span>
                        </div>
                        <p class="pricing-period text-muted">Harga disesuaikan kebutuhan</p>
                    </div>
                    <div class="pricing-card-body">
                        <ul class="pricing-feature-list">
                            <li><i class="fas fa-check-circle text-success"></i> Semua fitur paket Professional</li>
                            <li><i class="fas fa-check-circle text-success"></i> Premium custom theme + animasi</li>
                            <li><i class="fas fa-check-circle text-success"></i> Advanced features & plugins</li>
                            <li><i class="fas fa-check-circle text-success"></i> DOAJ submission support</li>
                            <li><i class="fas fa-check-circle text-success"></i> DOI registration & deposit</li>
                            <li><i class="fas fa-check-circle text-success"></i> Sinta submission support</li>
                            <li><i class="fas fa-check-circle text-success"></i> Analytics & reporting setup</li>
                            <li><i class="fas fa-check-circle text-success"></i> Priority support 24/7</li>
                            <li><i class="fas fa-check-circle text-success"></i> Maintenance 3 bulan</li>
                        </ul>
                        <a href="<?= $siteUrl ?>/konsultasi?paket=premium" class="btn btn-outline-primary w-100 mt-3">
                            <i class="fas fa-crown me-1"></i> Konsultasi Paket Premium
                        </a>
                    </div>
                </div>
            </div>

        </div>

        <!-- Note -->
        <div class="pricing-note text-center mt-4 fade-in-up">
            <p class="text-muted">
                <i class="fas fa-info-circle me-1"></i>
                Harga final disesuaikan berdasarkan kebutuhan spesifik jurnal Anda (jumlah artikel yang dimigrasi, tingkat kustomisasi tema, dll).
                <strong>Konsultasikan kebutuhan Anda</strong> untuk mendapatkan penawaran yang tepat.
            </p>
        </div>
    </div>
</section>

<!-- ══════════════════════════════════════════
     COMPARISON TABLE
══════════════════════════════════════════ -->
<section class="section-padding" style="background: var(--bg-light);">
    <div class="container">
        <div class="section-header text-center fade-in-up mb-5">
            <span class="section-badge">Perbandingan Paket</span>
            <h2 class="section-title">Fitur Lengkap Setiap Paket</h2>
        </div>
        <div class="table-responsive fade-in-up">
            <table class="pricing-compare-table">
                <thead>
                    <tr>
                        <th>Fitur</th>
                        <th class="text-center">Basic</th>
                        <th class="text-center" style="background:var(--primary); color:#fff;">Professional</th>
                        <th class="text-center">Premium</th>
                    </tr>
                </thead>
                <tbody>
                    <tr><td colspan="4" class="compare-section-header">Instalasi & Konfigurasi</td></tr>
                    <tr><td>Instalasi OJS 3.x terbaru</td><td class="text-center"><i class="fas fa-check text-success"></i></td><td class="text-center"><i class="fas fa-check text-success"></i></td><td class="text-center"><i class="fas fa-check text-success"></i></td></tr>
                    <tr><td>Konfigurasi server & database</td><td class="text-center"><i class="fas fa-check text-success"></i></td><td class="text-center"><i class="fas fa-check text-success"></i></td><td class="text-center"><i class="fas fa-check text-success"></i></td></tr>
                    <tr><td>SSL certificate & SMTP</td><td class="text-center"><i class="fas fa-check text-success"></i></td><td class="text-center"><i class="fas fa-check text-success"></i></td><td class="text-center"><i class="fas fa-check text-success"></i></td></tr>

                    <tr><td colspan="4" class="compare-section-header">Desain & Kustomisasi</td></tr>
                    <tr><td>Theme default OJS</td><td class="text-center"><i class="fas fa-check text-success"></i></td><td class="text-center"><i class="fas fa-check text-success"></i></td><td class="text-center"><i class="fas fa-check text-success"></i></td></tr>
                    <tr><td>Custom theme design</td><td class="text-center"><i class="fas fa-times text-muted"></i></td><td class="text-center"><i class="fas fa-check text-success"></i></td><td class="text-center"><i class="fas fa-check text-success"></i></td></tr>
                    <tr><td>Premium theme + animasi</td><td class="text-center"><i class="fas fa-times text-muted"></i></td><td class="text-center"><i class="fas fa-times text-muted"></i></td><td class="text-center"><i class="fas fa-check text-success"></i></td></tr>
                    <tr><td>Branding identitas institusi</td><td class="text-center"><i class="fas fa-times text-muted"></i></td><td class="text-center"><i class="fas fa-check text-success"></i></td><td class="text-center"><i class="fas fa-check text-success"></i></td></tr>

                    <tr><td colspan="4" class="compare-section-header">Integrasi & Indeksasi</td></tr>
                    <tr><td>SEO optimization</td><td class="text-center"><i class="fas fa-times text-muted"></i></td><td class="text-center"><i class="fas fa-check text-success"></i></td><td class="text-center"><i class="fas fa-check text-success"></i></td></tr>
                    <tr><td>Crossref DOI</td><td class="text-center"><i class="fas fa-times text-muted"></i></td><td class="text-center"><i class="fas fa-check text-success"></i></td><td class="text-center"><i class="fas fa-check text-success"></i></td></tr>
                    <tr><td>Google Scholar indexing</td><td class="text-center"><i class="fas fa-times text-muted"></i></td><td class="text-center"><i class="fas fa-check text-success"></i></td><td class="text-center"><i class="fas fa-check text-success"></i></td></tr>
                    <tr><td>DOAJ submission support</td><td class="text-center"><i class="fas fa-times text-muted"></i></td><td class="text-center"><i class="fas fa-times text-muted"></i></td><td class="text-center"><i class="fas fa-check text-success"></i></td></tr>
                    <tr><td>Sinta submission support</td><td class="text-center"><i class="fas fa-times text-muted"></i></td><td class="text-center"><i class="fas fa-times text-muted"></i></td><td class="text-center"><i class="fas fa-check text-success"></i></td></tr>
                    <tr><td>Advanced plugins (ORCID, PKP PN)</td><td class="text-center"><i class="fas fa-times text-muted"></i></td><td class="text-center"><i class="fas fa-times text-muted"></i></td><td class="text-center"><i class="fas fa-check text-success"></i></td></tr>

                    <tr><td colspan="4" class="compare-section-header">Pelatihan & Support</td></tr>
                    <tr><td>Training tim editorial</td><td class="text-center">2 jam</td><td class="text-center">4 jam</td><td class="text-center">Lengkap</td></tr>
                    <tr><td>Periode support teknis</td><td class="text-center">30 hari</td><td class="text-center">60 hari</td><td class="text-center">Priority 24/7</td></tr>
                    <tr><td>Maintenance berkala</td><td class="text-center"><i class="fas fa-times text-muted"></i></td><td class="text-center"><i class="fas fa-times text-muted"></i></td><td class="text-center">3 bulan</td></tr>
                    <tr><td>Analytics & reporting</td><td class="text-center"><i class="fas fa-times text-muted"></i></td><td class="text-center"><i class="fas fa-times text-muted"></i></td><td class="text-center"><i class="fas fa-check text-success"></i></td></tr>
                </tbody>
            </table>
        </div>
    </div>
</section>

<!-- ══════════════════════════════════════════
     LAYANAN TAMBAHAN WITH TIME ESTIMATES
══════════════════════════════════════════ -->
<section class="section-padding bg-white">
    <div class="container">
        <div class="section-header text-center fade-in-up mb-5">
            <span class="section-badge">Layanan Tambahan</span>
            <h2 class="section-title">Bisa Dikombinasikan Sesuai Kebutuhan</h2>
            <p class="section-subtitle">Setiap layanan bisa dipesan terpisah atau dikombinasikan dengan paket utama Anda.</p>
        </div>
        <div class="row g-3 justify-content-center">
            <?php
            $extras = [
                ['icon' => 'fas fa-server',           'title' => 'Setup & Instalasi OJS',       'time' => '7–14 hari kerja',        'desc' => 'Instalasi OJS 3.x dari nol di hosting Anda. Setup database, konfigurasi domain, dan pengaturan awal editorial.'],
                ['icon' => 'fas fa-globe',             'title' => 'Indeksasi DOAJ',               'time' => '30–90 hari proses',      'desc' => 'Audit menyeluruh, penyusunan dokumen kebijakan, dan pendampingan penuh proses pengajuan DOAJ.'],
                ['icon' => 'fas fa-award',             'title' => 'Pendampingan Sinta',           'time' => '14–30 hari kerja',       'desc' => 'Audit metadata, perbaikan struktur website, dan pendampingan pengajuan atau kenaikan peringkat Sinta.'],
                ['icon' => 'fas fa-exchange-alt',      'title' => 'Migrasi OJS 2 → OJS 3',       'time' => '14–21 hari kerja',       'desc' => 'Migrasi zero-data-loss dari OJS 2.4.x ke OJS 3.3. Backup berlapis dan staging environment.'],
                ['icon' => 'fas fa-paint-brush',       'title' => 'Desain Tema Kustom',           'time' => '7–10 hari kerja',        'desc' => 'Desain tema OJS sesuai identitas institusi. Responsif, cepat, dan memenuhi standar aksesibilitas.'],
                ['icon' => 'fas fa-plug',              'title' => 'Integrasi Plugin & DOI',       'time' => '3–5 hari kerja',         'desc' => 'Setup Crossref DOI, Google Scholar, ORCID, PKP PN, dan plugin lainnya sesuai kebutuhan.'],
                ['icon' => 'fas fa-chalkboard-teacher', 'title' => 'Konsultasi & Pelatihan Tim', 'time' => 'Fleksibel / Zoom',       'desc' => 'Sesi pelatihan tim editorial untuk pengelolaan OJS, alur submission, manajemen terbitan, dan DOI.'],
                ['icon' => 'fas fa-shield-alt',        'title' => 'Maintenance & Support Bulanan', 'time' => 'Kontrak min. 3 bulan', 'desc' => 'Pemantauan server, update OJS & plugin, backup rutin, dan support teknis prioritas setiap bulan.'],
            ];
            foreach ($extras as $i => $ex):
            ?>
            <div class="col-md-6 col-lg-3">
                <div class="text-center p-4 bg-light rounded-3 h-100 fade-in-up" style="animation-delay:<?= $i * 0.06 ?>s; border: 1px solid var(--border);">
                    <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3" style="width:56px; height:56px; background: rgba(13,148,136,0.1); color: var(--secondary); font-size:1.25rem;">
                        <i class="<?= $ex['icon'] ?>"></i>
                    </div>
                    <h6 class="fw-700 mb-1"><?= $ex['title'] ?></h6>
                    <span class="badge bg-white text-muted mb-2 shadow-sm" style="font-size:0.72rem;"><i class="fas fa-clock me-1"></i><?= $ex['time'] ?></span>
                    <p class="small text-muted mb-0"><?= $ex['desc'] ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ══════════════════════════════════════════
     FAQ
══════════════════════════════════════════ -->
<section class="section-padding" style="background: var(--bg-light);">
    <div class="container">
        <div class="section-header text-center fade-in-up mb-5">
            <span class="section-badge">Pertanyaan Umum</span>
            <h2 class="section-title">Pertanyaan Seputar Harga & Pembayaran</h2>
        </div>
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="accordion faq-accordion fade-in-up" id="faqHarga">

                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faqH1">
                                Mengapa tidak ada harga pasti di website?
                            </button>
                        </h2>
                        <div id="faqH1" class="accordion-collapse collapse show" data-bs-parent="#faqHarga">
                            <div class="accordion-body">
                                Setiap jurnal memiliki kebutuhan yang unik — jumlah artikel, tingkat kustomisasi, kompleksitas migrasi, dan target indeksasi berbeda-beda. Dengan konsultasi terlebih dahulu, kami dapat memberikan <strong>penawaran yang tepat dan transparan</strong> sesuai kebutuhan spesifik Anda, bukan harga one-size-fits-all.
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faqH2">
                                Apakah saya perlu menyediakan hosting sendiri?
                            </button>
                        </h2>
                        <div id="faqH2" class="accordion-collapse collapse" data-bs-parent="#faqHarga">
                            <div class="accordion-body">
                                Idealnya jurnal menggunakan hosting dari institusi Anda sendiri, terutama jika menargetkan akreditasi SINTA (yang mensyaratkan domain .ac.id). Jika belum memiliki infrastruktur, kami dapat <strong>merekomendasikan layanan hosting yang sesuai</strong> atau membantu pengajuan ke pengelola IT institusi. Biaya hosting dan domain tidak termasuk dalam paket kami.
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faqH3">
                                Bagaimana metode pembayarannya?
                            </button>
                        </h2>
                        <div id="faqH3" class="accordion-collapse collapse" data-bs-parent="#faqHarga">
                            <div class="accordion-body">
                                Kami menerima pembayaran via <strong>transfer bank</strong>. Untuk proyek di atas nilai tertentu, pembayaran bisa dilakukan secara bertahap (DP + pelunasan setelah serah terima). Detail pembayaran akan disampaikan dalam proposal kerja setelah konsultasi.
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faqH4">
                                Apakah bisa menggabungkan beberapa layanan?
                            </button>
                        </h2>
                        <div id="faqH4" class="accordion-collapse collapse" data-bs-parent="#faqHarga">
                            <div class="accordion-body">
                                Tentu! Semua layanan tambahan bisa <strong>dikombinasikan</strong> dengan paket utama maupun dipesan terpisah. Kami juga memberikan penawaran khusus untuk paket bundling. Konsultasikan kebutuhan Anda agar kami bisa menyusun paket yang paling optimal dari segi biaya dan cakupan.
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faqH5">
                                Berapa lama waktu untuk mendapatkan penawaran?
                            </button>
                        </h2>
                        <div id="faqH5" class="accordion-collapse collapse" data-bs-parent="#faqHarga">
                            <div class="accordion-body">
                                Setelah sesi konsultasi gratis (30 menit), kami biasanya mengirimkan <strong>proposal tertulis dalam 1–3 hari kerja</strong> lengkap dengan rincian layanan, timeline, dan estimasi biaya. Anda tidak perlu berkomitmen apa pun saat konsultasi.
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA -->
<section class="cta-section">
    <div class="cta-shapes" aria-hidden="true">
        <div class="cta-shape cta-shape-1"></div>
        <div class="cta-shape cta-shape-2"></div>
    </div>
    <div class="container text-center">
        <div class="cta-content fade-in-up">
            <h2 class="cta-title">Tidak Yakin Paket Mana yang Tepat?</h2>
            <p class="cta-subtitle">Konsultasi gratis 30 menit. Kami bantu analisis kebutuhan jurnal dan rekomendasikan paket yang paling sesuai budget Anda.</p>
            <div class="cta-actions">
                <a href="<?= $siteUrl ?>/konsultasi" class="btn btn-accent btn-lg">
                    <i class="fas fa-comments me-2"></i>Konsultasi Gratis Sekarang
                </a>
            </div>
        </div>
    </div>
</section>
