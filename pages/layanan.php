<?php
/**
 * Layanan (Services) Page
 * Adapted from Snada Studio reference – services focused on OJS ecosystem
 */
$pageTitle = 'Layanan – ' . getSetting('site_name', 'OJS Developer Indonesia');
$metaDesc  = 'Layanan lengkap untuk infrastruktur jurnal OJS: setup, indeksasi DOAJ/Sinta, migrasi, desain tema kustom, konsultasi, dan maintenance profesional.';
$siteUrl   = defined('SITE_URL') ? SITE_URL : '';
?>

<!-- Page Header -->
<div class="page-hero">
    <div class="container">
        <div class="page-hero-content text-center fade-in-up">
            <h1 class="page-hero-title">Layanan Lengkap untuk Infrastruktur Jurnal</h1>
            <p class="page-hero-subtitle">Dari instalasi awal hingga berhasil terindeks — kami menangani seluruh kebutuhan teknis jurnal OJS Anda dengan standar internasional.</p>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb justify-content-center">
                    <li class="breadcrumb-item"><a href="<?= $siteUrl ?>/">Beranda</a></li>
                    <li class="breadcrumb-item active">Layanan</li>
                </ol>
            </nav>
        </div>
    </div>
</div>

<!-- ══════════════════════════════════════════
     SERVICES OVERVIEW – 6 Core Services
══════════════════════════════════════════ -->
<section class="section-padding bg-white">
    <div class="container">
        <div class="section-header text-center fade-in-up">
            <span class="section-badge">Solusi Komprehensif</span>
            <h2 class="section-title">Apa yang Kami Tawarkan</h2>
            <p class="section-subtitle">Solusi komprehensif untuk ekosistem jurnal akademik Indonesia — setiap layanan dirancang untuk memenuhi standar akreditasi dan indeksasi internasional.</p>
        </div>
        <div class="row g-4 mt-2">

            <!-- 1. Setup & Instalasi OJS -->
            <div class="col-lg-6" id="setup">
                <div class="service-detail-card fade-in-up">
                    <div class="service-detail-header" style="background: linear-gradient(135deg, var(--primary), #2a4a7f);">
                        <div class="service-detail-icon">
                            <i class="fas fa-server fa-2x"></i>
                        </div>
                        <div>
                            <h3 class="service-detail-title">Setup & Instalasi OJS</h3>
                            <p class="service-detail-tagline mb-0">Estimasi: 7–14 hari kerja</p>
                        </div>
                    </div>
                    <div class="service-detail-body">
                        <p>
                            Instalasi OJS 3.x dari nol di hosting Anda. Kami melakukan setup database, konfigurasi domain, SSL certificate, email SMTP, dan pengaturan awal alur editorial — sehingga jurnal Anda siap menerima submisi sejak hari pertama.
                        </p>
                        <p>
                            Setiap instalasi dikonfigurasi dengan standar keamanan terbaik, performa optimal, dan kompatibel dengan kebutuhan akreditasi jurnal di Indonesia.
                        </p>
                        <h6 class="mt-3 mb-2 fw-700">Yang Anda Dapatkan:</h6>
                        <ul class="service-feature-list">
                            <li><i class="fas fa-check-circle"></i> Instalasi OJS 3.x versi terbaru & stabil</li>
                            <li><i class="fas fa-check-circle"></i> Konfigurasi server, database & domain</li>
                            <li><i class="fas fa-check-circle"></i> SSL certificate & email SMTP setup</li>
                            <li><i class="fas fa-check-circle"></i> Setup tim editorial & alur submission</li>
                            <li><i class="fas fa-check-circle"></i> Training dasar & dokumentasi lengkap</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- 2. Indeksasi DOAJ -->
            <div class="col-lg-6" id="indeksasi-doaj">
                <div class="service-detail-card fade-in-up" style="animation-delay:0.1s">
                    <div class="service-detail-header" style="background: linear-gradient(135deg, #059669, #047857);">
                        <div class="service-detail-icon">
                            <i class="fas fa-globe fa-2x"></i>
                        </div>
                        <div>
                            <h3 class="service-detail-title">Indeksasi DOAJ</h3>
                            <p class="service-detail-tagline mb-0">Estimasi: 30–90 hari proses</p>
                        </div>
                    </div>
                    <div class="service-detail-body">
                        <p>
                            Audit menyeluruh, penyusunan dokumen kebijakan, dan pendampingan penuh proses pengajuan DOAJ. Kami memastikan semua kriteria terpenuhi sebelum pengajuan sehingga peluang diterima maksimal.
                        </p>
                        <p>
                            Proses pengajuan DOAJ biasanya memakan waktu 2–6 bulan dari tim DOAJ. Tugas kami adalah memastikan jurnal Anda memenuhi semua persyaratan teknis dan administratif sebelum mengajukan.
                        </p>
                        <h6 class="mt-3 mb-2 fw-700">Yang Anda Dapatkan:</h6>
                        <ul class="service-feature-list">
                            <li><i class="fas fa-check-circle"></i> Audit kelengkapan & kesiapan jurnal</li>
                            <li><i class="fas fa-check-circle"></i> Penyusunan kebijakan jurnal (OA, etika, peer review)</li>
                            <li><i class="fas fa-check-circle"></i> Optimasi metadata & OAI-PMH harvesting</li>
                            <li><i class="fas fa-check-circle"></i> Pendampingan penuh proses pengajuan</li>
                            <li><i class="fas fa-check-circle"></i> Revisi & follow-up hingga diterima</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- 3. Pendampingan Sinta -->
            <div class="col-lg-6" id="sinta">
                <div class="service-detail-card fade-in-up">
                    <div class="service-detail-header" style="background: linear-gradient(135deg, #7c3aed, #5b21b6);">
                        <div class="service-detail-icon">
                            <i class="fas fa-award fa-2x"></i>
                        </div>
                        <div>
                            <h3 class="service-detail-title">Pendampingan Sinta</h3>
                            <p class="service-detail-tagline mb-0">Estimasi: 14–30 hari kerja</p>
                        </div>
                    </div>
                    <div class="service-detail-body">
                        <p>
                            Audit metadata, perbaikan struktur website, dan pendampingan pengajuan atau kenaikan peringkat Sinta. Kami membantu persiapan teknis dan administratif sesuai pedoman ARJUNA/SINTA Kemendikbud.
                        </p>
                        <p>
                            Bagi institusi yang menargetkan akreditasi nasional, kami memastikan jurnal Anda memenuhi semua kriteria teknis yang dipersyaratkan oleh sistem penilaian SINTA.
                        </p>
                        <h6 class="mt-3 mb-2 fw-700">Yang Anda Dapatkan:</h6>
                        <ul class="service-feature-list">
                            <li><i class="fas fa-check-circle"></i> Audit metadata & struktur website</li>
                            <li><i class="fas fa-check-circle"></i> Perbaikan sesuai kriteria SINTA</li>
                            <li><i class="fas fa-check-circle"></i> Pendampingan pengajuan/kenaikan peringkat</li>
                            <li><i class="fas fa-check-circle"></i> Setup Google Scholar & Crossref</li>
                            <li><i class="fas fa-check-circle"></i> Panduan persiapan akreditasi lengkap</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- 4. Migrasi OJS -->
            <div class="col-lg-6" id="migrasi">
                <div class="service-detail-card fade-in-up" style="animation-delay:0.1s">
                    <div class="service-detail-header" style="background: linear-gradient(135deg, #d97706, #b45309);">
                        <div class="service-detail-icon">
                            <i class="fas fa-exchange-alt fa-2x"></i>
                        </div>
                        <div>
                            <h3 class="service-detail-title">Migrasi OJS 2 &rarr; OJS 3</h3>
                            <p class="service-detail-tagline mb-0">Estimasi: 14–21 hari kerja</p>
                        </div>
                    </div>
                    <div class="service-detail-body">
                        <p>
                            Migrasi zero-data-loss dari OJS 2.4.x ke OJS 3.3+. Kami menggunakan protokol backup berlapis dan staging environment sebelum go-live — tidak ada data yang hilang dalam semua proyek migrasi yang pernah kami kerjakan.
                        </p>
                        <p>
                            Seluruh artikel, metadata, pengguna, edisi, dan file PDF dipindahkan dengan integritas penuh. Proses dilakukan dengan tahapan backup, verifikasi data, pengujian, dan serah terima yang terdokumentasi.
                        </p>
                        <h6 class="mt-3 mb-2 fw-700">Yang Anda Dapatkan:</h6>
                        <ul class="service-feature-list">
                            <li><i class="fas fa-check-circle"></i> Migrasi artikel, metadata & file PDF</li>
                            <li><i class="fas fa-check-circle"></i> Migrasi pengguna & data editorial</li>
                            <li><i class="fas fa-check-circle"></i> Backup berlapis & staging environment</li>
                            <li><i class="fas fa-check-circle"></i> Verifikasi integritas data pasca-migrasi</li>
                            <li><i class="fas fa-check-circle"></i> Redirect URL lama ke URL baru</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- 5. Desain Tema Kustom -->
            <div class="col-lg-6" id="desain">
                <div class="service-detail-card fade-in-up">
                    <div class="service-detail-header" style="background: linear-gradient(135deg, var(--secondary), #0a7a70);">
                        <div class="service-detail-icon">
                            <i class="fas fa-paint-brush fa-2x"></i>
                        </div>
                        <div>
                            <h3 class="service-detail-title">Desain Tema Kustom</h3>
                            <p class="service-detail-tagline mb-0">Estimasi: 7–10 hari kerja</p>
                        </div>
                    </div>
                    <div class="service-detail-body">
                        <p>
                            Desain tema OJS yang mencerminkan identitas institusi Anda. Responsif, cepat, dan memenuhi standar aksesibilitas — tampil optimal di desktop, tablet, dan smartphone.
                        </p>
                        <p>
                            Semua desain dibuat custom sesuai branding institusi — mulai dari logo, palet warna, tipografi, hingga tata letak halaman yang modern dan profesional, meningkatkan kepercayaan pembaca dan calon penulis.
                        </p>
                        <h6 class="mt-3 mb-2 fw-700">Yang Anda Dapatkan:</h6>
                        <ul class="service-feature-list">
                            <li><i class="fas fa-check-circle"></i> Custom theme sesuai identitas institusi</li>
                            <li><i class="fas fa-check-circle"></i> Responsive & mobile-friendly</li>
                            <li><i class="fas fa-check-circle"></i> Optimasi kecepatan halaman</li>
                            <li><i class="fas fa-check-circle"></i> Logo & favicon profesional</li>
                            <li><i class="fas fa-check-circle"></i> Revisi desain hingga 3 kali</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- 6. Konsultasi & Pelatihan -->
            <div class="col-lg-6" id="konsultasi-pelatihan">
                <div class="service-detail-card fade-in-up" style="animation-delay:0.1s">
                    <div class="service-detail-header" style="background: linear-gradient(135deg, #0e7490, #0a5570);">
                        <div class="service-detail-icon">
                            <i class="fas fa-chalkboard-teacher fa-2x"></i>
                        </div>
                        <div>
                            <h3 class="service-detail-title">Konsultasi & Pelatihan Tim</h3>
                            <p class="service-detail-tagline mb-0">Fleksibel / via Zoom</p>
                        </div>
                    </div>
                    <div class="service-detail-body">
                        <p>
                            Sesi pelatihan tim editorial untuk pengelolaan OJS — mencakup alur submission, manajemen terbitan, konfigurasi DOI, dan pengelolaan sehari-hari. Tersedia secara online maupun tatap muka.
                        </p>
                        <p>
                            Dengan pelatihan dari kami, tim Anda akan mampu mengelola seluruh alur editorial secara mandiri — dari penerimaan manuskrip, proses review, hingga publikasi dan pengelolaan DOI.
                        </p>
                        <h6 class="mt-3 mb-2 fw-700">Yang Anda Dapatkan:</h6>
                        <ul class="service-feature-list">
                            <li><i class="fas fa-check-circle"></i> Pelatihan admin, editor & reviewer</li>
                            <li><i class="fas fa-check-circle"></i> Modul pelatihan Bahasa Indonesia</li>
                            <li><i class="fas fa-check-circle"></i> Simulasi alur kerja editorial nyata</li>
                            <li><i class="fas fa-check-circle"></i> Rekaman sesi pelatihan (online)</li>
                            <li><i class="fas fa-check-circle"></i> Q&A pasca-pelatihan 30 hari</li>
                        </ul>
                    </div>
                </div>
            </div>

        </div><!-- /row -->
    </div>
</section>

<!-- ══════════════════════════════════════════
     ADDITIONAL SERVICES
══════════════════════════════════════════ -->
<section class="section-padding" style="background: var(--bg-light);">
    <div class="container">
        <div class="section-header text-center fade-in-up">
            <span class="section-badge">Layanan Tambahan</span>
            <h2 class="section-title">Bisa Dikombinasikan Sesuai Kebutuhan</h2>
            <p class="section-subtitle">Pilih layanan tambahan yang paling relevan untuk kebutuhan jurnal Anda.</p>
        </div>
        <div class="row g-3 mt-3">
            <?php
            $additionalServices = [
                ['icon' => 'fas fa-plug',         'title' => 'Integrasi Plugin & DOI',       'time' => '3–5 hari kerja',  'desc' => 'Setup Crossref DOI, Google Scholar, ORCID, PKP PN, dan plugin lainnya.'],
                ['icon' => 'fas fa-shield-alt',    'title' => 'Maintenance & Support',        'time' => 'Kontrak min. 3 bulan', 'desc' => 'Pemantauan server, update OJS & plugin, backup rutin, dan support teknis prioritas.'],
                ['icon' => 'fas fa-search',        'title' => 'SEO & Google Scholar',         'time' => '5–7 hari kerja',  'desc' => 'Optimasi metadata, sitemap XML, schema markup untuk visibilitas maksimal.'],
                ['icon' => 'fas fa-file-alt',      'title' => 'Penyusunan Dokumen Kebijakan', 'time' => '3–5 hari kerja',  'desc' => 'Kebijakan OA, etika publikasi, peer review, dan hak cipta sesuai standar DOAJ.'],
            ];
            foreach ($additionalServices as $i => $svc):
            ?>
            <div class="col-md-6">
                <div class="d-flex align-items-start gap-3 p-3 bg-white rounded-3 shadow-sm fade-in-up" style="animation-delay:<?= $i * 0.08 ?>s; border: 1px solid var(--border);">
                    <div class="flex-shrink-0 d-flex align-items-center justify-content-center rounded-3" style="width:48px; height:48px; background: rgba(13,148,136,0.1); color: var(--secondary);">
                        <i class="<?= $svc['icon'] ?>"></i>
                    </div>
                    <div>
                        <h6 class="mb-1 fw-700"><?= $svc['title'] ?></h6>
                        <span class="badge bg-light text-muted mb-2" style="font-size:0.75rem;"><i class="fas fa-clock me-1"></i><?= $svc['time'] ?></span>
                        <p class="mb-0 small text-muted"><?= $svc['desc'] ?></p>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ══════════════════════════════════════════
     HOW WE WORK — 5-Step Process
══════════════════════════════════════════ -->
<section class="section-padding bg-white">
    <div class="container">
        <div class="section-header text-center fade-in-up">
            <span class="section-badge">Alur Kerja Kami</span>
            <h2 class="section-title">Bagaimana Kami Bekerja</h2>
            <p class="section-subtitle">Proses transparan dan terstruktur untuk memastikan hasil terbaik di setiap proyek</p>
        </div>
        <div class="process-timeline mt-5">
            <?php
            $steps = [
                ['num' => '01', 'icon' => 'fas fa-comments',        'title' => 'Konsultasi Gratis',    'desc' => 'Sesi 30 menit untuk memahami kondisi dan kebutuhan jurnal Anda. Kami mendengarkan, menganalisis, dan memberikan rekomendasi layanan yang paling sesuai — tanpa biaya apapun.'],
                ['num' => '02', 'icon' => 'fas fa-clipboard-list',  'title' => 'Audit & Proposal',     'desc' => 'Kami audit kondisi jurnal Anda dan siapkan proposal kerja yang detail — mencakup scope of work, timeline, milestone, dan rincian biaya sebelum pekerjaan dimulai.'],
                ['num' => '03', 'icon' => 'fas fa-laptop-code',     'title' => 'Pengerjaan',           'desc' => 'Tim kami bekerja sesuai timeline yang telah disepakati bersama. Anda akan mendapatkan update progress secara berkala dan akses ke staging environment untuk memantau perkembangan.'],
                ['num' => '04', 'icon' => 'fas fa-sync-alt',        'title' => 'Review & Revisi',      'desc' => 'Anda cek hasilnya, kami revisi hingga sesuai ekspektasi. Pengujian menyeluruh dilakukan untuk memastikan semua fitur berfungsi dengan baik.'],
                ['num' => '05', 'icon' => 'fas fa-flag-checkered',  'title' => 'Serah Terima',         'desc' => 'Jurnal diserahterimakan lengkap dengan dokumentasi dan pelatihan. Kami memberikan garansi support selama 30 hari setelah serah terima.'],
            ];
            foreach ($steps as $i => $step):
            ?>
            <div class="process-step fade-in-up" style="animation-delay:<?= $i * 0.12 ?>s">
                <div class="process-step-number"><?= $step['num'] ?></div>
                <div class="process-step-content">
                    <div class="process-step-icon">
                        <i class="<?= $step['icon'] ?>"></i>
                    </div>
                    <h4 class="process-step-title"><?= $step['title'] ?></h4>
                    <p class="process-step-desc"><?= $step['desc'] ?></p>
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
            <h2 class="section-title">Pertanyaan Seputar Layanan</h2>
        </div>
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="accordion faq-accordion fade-in-up" id="faqLayanan">

                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faqL1">
                                Berapa lama waktu pengerjaan setup standar?
                            </button>
                        </h2>
                        <div id="faqL1" class="accordion-collapse collapse show" data-bs-parent="#faqLayanan">
                            <div class="accordion-body">
                                Untuk setup standar, kami biasanya menyelesaikan dalam <strong>7–14 hari kerja</strong>, tergantung kompleksitas konfigurasi dan kelengkapan data yang Anda berikan. Proyek yang lebih kompleks seperti migrasi atau kustomisasi tema membutuhkan waktu yang lebih lama sesuai estimasi masing-masing layanan.
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faqL2">
                                Apakah data jurnal saya aman selama proses migrasi?
                            </button>
                        </h2>
                        <div id="faqL2" class="accordion-collapse collapse" data-bs-parent="#faqLayanan">
                            <div class="accordion-body">
                                Kami menggunakan protokol <strong>backup berlapis</strong> dan selalu melakukan pengerjaan di <strong>staging environment</strong> sebelum go-live. Tidak ada data yang hilang dalam semua proyek migrasi yang pernah kami kerjakan. Setiap migrasi juga dilengkapi dengan laporan verifikasi integritas data.
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faqL3">
                                Apakah ada garansi setelah pekerjaan selesai?
                            </button>
                        </h2>
                        <div id="faqL3" class="accordion-collapse collapse" data-bs-parent="#faqLayanan">
                            <div class="accordion-body">
                                Ya, kami memberikan <strong>garansi support selama 30 hari</strong> setelah serah terima. Selama periode itu, kami akan memperbaiki bug atau masalah teknis yang timbul secara gratis. Untuk kebutuhan di luar masa garansi, tersedia layanan maintenance berbayar dengan harga kompetitif.
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faqL4">
                                Berapa lama proses pengajuan DOAJ?
                            </button>
                        </h2>
                        <div id="faqL4" class="accordion-collapse collapse" data-bs-parent="#faqLayanan">
                            <div class="accordion-body">
                                Proses pengajuan DOAJ biasanya memakan waktu <strong>2–6 bulan</strong> dari tim DOAJ (bukan dari kami). Tugas kami adalah memastikan semua kriteria terpenuhi sebelum pengajuan sehingga peluang diterima maksimal. Kami juga mendampingi proses follow-up hingga jurnal Anda berhasil terindeks.
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faqL5">
                                Apakah saya bisa konsultasi dulu sebelum memutuskan?
                            </button>
                        </h2>
                        <div id="faqL5" class="accordion-collapse collapse" data-bs-parent="#faqLayanan">
                            <div class="accordion-body">
                                Tentu! Kami menyediakan <strong>sesi konsultasi gratis 30 menit</strong> tanpa komitmen apapun. Anda bisa bertanya segala hal tentang OJS, indeksasi, atau kondisi jurnal Anda saat ini. Setelah konsultasi, kami akan memberikan rekomendasi layanan yang paling sesuai dengan kebutuhan dan anggaran Anda.
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
            <h2 class="cta-title">Siap Memulai? Konsultasi Gratis Menunggu Anda</h2>
            <p class="cta-subtitle">30 menit sesi konsultasi gratis. Tanpa komitmen apapun. Kami bantu analisis kebutuhan jurnal dan rekomendasikan solusi terbaik.</p>
            <div class="cta-actions">
                <a href="<?= $siteUrl ?>/konsultasi" class="btn btn-accent btn-lg">
                    <i class="fas fa-comments me-2"></i>Mulai Konsultasi
                </a>
                <a href="<?= $siteUrl ?>/harga" class="btn btn-outline-light btn-lg">
                    <i class="fas fa-tags me-2"></i>Lihat Paket Harga
                </a>
            </div>
        </div>
    </div>
</section>
