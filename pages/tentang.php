<?php
/**
 * Tentang (About) Page
 */
$pageTitle = 'Tentang Kami – ' . getSetting('site_name', 'OJS Developer Indonesia');
$siteUrl   = defined('SITE_URL') ? SITE_URL : '';
?>

<!-- Page Hero -->
<div class="page-hero">
    <div class="container">
        <div class="page-hero-content text-center fade-in-up">
            <h1 class="page-hero-title">Tentang Kami</h1>
            <p class="page-hero-subtitle">Mengenal lebih dekat tim di balik OJS Developer Indonesia</p>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb justify-content-center">
                    <li class="breadcrumb-item"><a href="<?= $siteUrl ?>/">Beranda</a></li>
                    <li class="breadcrumb-item active">Tentang Kami</li>
                </ol>
            </nav>
        </div>
    </div>
</div>

<!-- ══════════════════════════════════════════
     ABOUT INTRO
══════════════════════════════════════════ -->
<section class="section-padding bg-white">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-6 fade-in-up">
                <span class="section-badge">Siapa Kami</span>
                <h2 class="section-title">Mitra Terpercaya Jurnal Akademik Indonesia</h2>
                <p class="text-muted mb-3">
                    OJS Developer Indonesia adalah tim spesialis yang berfokus pada pengembangan, implementasi, dan pengelolaan website jurnal ilmiah berbasis Open Journal Systems (OJS) untuk komunitas akademik Indonesia.
                </p>
                <p class="text-muted mb-3">
                    Didirikan pada tahun 2019 oleh sekelompok praktisi teknologi yang berlatar belakang akademik, kami hadir sebagai respons terhadap kebutuhan mendesak perguruan tinggi dan lembaga penelitian Indonesia untuk memiliki website jurnal yang profesional, terstandarisasi, dan memenuhi persyaratan akreditasi nasional maupun internasional.
                </p>
                <p class="text-muted">
                    Dalam perjalanan lima tahun lebih, kami telah membangun dan mengelola lebih dari 150 jurnal ilmiah dari berbagai bidang keilmuan — mulai dari pendidikan, kesehatan, teknik, hukum, ekonomi, hingga ilmu sosial dan humaniora.
                </p>
            </div>
            <div class="col-lg-6 fade-in-right">
                <div class="about-visual">
                    <div class="about-visual-grid">
                        <div class="about-stat-block">
                            <div class="about-stat-num">150+</div>
                            <div class="about-stat-lab">Jurnal Dibangun</div>
                        </div>
                        <div class="about-stat-block accent">
                            <div class="about-stat-num">50+</div>
                            <div class="about-stat-lab">Institusi Partner</div>
                        </div>
                        <div class="about-stat-block secondary">
                            <div class="about-stat-num">5+</div>
                            <div class="about-stat-lab">Tahun Pengalaman</div>
                        </div>
                        <div class="about-stat-block">
                            <div class="about-stat-num">99%</div>
                            <div class="about-stat-lab">Kepuasan Klien</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ══════════════════════════════════════════
     VISION & MISSION
══════════════════════════════════════════ -->
<section class="section-padding" style="background: var(--bg-light);">
    <div class="container">
        <div class="section-header text-center fade-in-up mb-5">
            <span class="section-badge">Arah & Tujuan</span>
            <h2 class="section-title">Visi & Misi Kami</h2>
        </div>
        <div class="row g-4">
            <div class="col-md-6">
                <div class="vm-card vm-card-vision fade-in-up">
                    <div class="vm-card-icon">
                        <i class="fas fa-eye"></i>
                    </div>
                    <h3>Visi</h3>
                    <p>
                        Menjadi platform layanan jurnal OJS terdepan di Indonesia yang mendorong akselerasi digitalisasi publikasi ilmiah nasional, meningkatkan visibilitas riset Indonesia di panggung global, dan berkontribusi pada kemajuan ekosistem pengetahuan akademik bangsa.
                    </p>
                </div>
            </div>
            <div class="col-md-6">
                <div class="vm-card vm-card-mission fade-in-up" style="animation-delay:0.1s">
                    <div class="vm-card-icon">
                        <i class="fas fa-bullseye"></i>
                    </div>
                    <h3>Misi</h3>
                    <ul class="vm-mission-list">
                        <li>Menyediakan layanan teknis OJS yang berkualitas tinggi, terjangkau, dan mudah diakses oleh seluruh institusi akademik di Indonesia.</li>
                        <li>Memberdayakan tim editorial jurnal melalui pelatihan yang komprehensif sehingga dapat mengelola jurnal secara mandiri.</li>
                        <li>Mendukung upaya akreditasi dan indexing jurnal nasional untuk meningkatkan dampak riset Indonesia di tingkat internasional.</li>
                        <li>Terus berinovasi menghadirkan solusi teknologi terkini untuk mendukung ekosistem publikasi ilmiah yang berkelanjutan.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ══════════════════════════════════════════
     WHY CHOOSE US
══════════════════════════════════════════ -->
<section class="section-padding bg-white">
    <div class="container">
        <div class="section-header text-center fade-in-up mb-5">
            <span class="section-badge">Keunggulan Kami</span>
            <h2 class="section-title">Mengapa Kami Berbeda</h2>
            <p class="section-subtitle">Lima alasan utama mengapa ratusan jurnal akademik mempercayakan kebutuhan OJS mereka kepada kami</p>
        </div>
        <div class="row g-4">
            <?php
            $whyUs = [
                ['icon' => 'fas fa-microscope',       'title' => 'Spesialisasi Eksklusif OJS',        'desc' => 'Kami tidak mengerjakan semua jenis website. Fokus eksklusif kami pada OJS menjadikan kami ahli yang benar-benar memahami seluk-beluk platform ini secara mendalam — dari instalasi hingga konfigurasi lanjutan.'],
                ['icon' => 'fas fa-users-cog',         'title' => 'Tim Berlatar Belakang Akademik',    'desc' => 'Anggota tim kami memiliki pengalaman langsung di dunia akademik — sebagai peneliti, penulis, maupun editor jurnal. Pemahaman ini membuat kami bisa berempati dan memberikan solusi yang benar-benar relevan.'],
                ['icon' => 'fas fa-graduation-cap',    'title' => 'Memahami Regulasi Nasional',        'desc' => 'Kami selalu mengikuti perkembangan regulasi akreditasi jurnal dari ARJUNA/SINTA Kemendikbud, sehingga setiap jurnal yang kami bangun sudah siap untuk proses akreditasi nasional.'],
                ['icon' => 'fas fa-handshake-angle',   'title' => 'Pendampingan Jangka Panjang',      'desc' => 'Hubungan kami dengan klien tidak berhenti setelah proyek selesai. Kami memberikan pendampingan berkelanjutan — mulai dari konsultasi gratis, pelatihan lanjutan, hingga support teknis saat dibutuhkan.'],
                ['icon' => 'fas fa-shield-halved',     'title' => 'Transparansi & Kejujuran',         'desc' => 'Kami percaya hubungan bisnis yang sehat dibangun atas dasar kejujuran. Anda akan selalu mendapatkan informasi yang jelas tentang apa yang kami kerjakan, berapa biayanya, dan berapa lama waktu yang dibutuhkan.'],
            ];
            foreach ($whyUs as $i => $item):
            ?>
            <div class="col-md-6 col-lg-4">
                <div class="why-card fade-in-up" style="animation-delay:<?= $i * 0.08 ?>s">
                    <div class="why-card-icon">
                        <i class="<?= $item['icon'] ?>"></i>
                    </div>
                    <h4 class="why-card-title"><?= $item['title'] ?></h4>
                    <p class="why-card-desc"><?= $item['desc'] ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ══════════════════════════════════════════
     TEAM
══════════════════════════════════════════ -->
<section class="section-padding" style="background: var(--bg-light);">
    <div class="container">
        <div class="section-header text-center fade-in-up mb-5">
            <span class="section-badge">Tim Kami</span>
            <h2 class="section-title">Kenali Tim di Balik OJS Developer Indonesia</h2>
            <p class="section-subtitle">Didukung oleh profesional berpengalaman yang berdedikasi untuk kesuksesan jurnal Anda</p>
        </div>
        <div class="row g-4 justify-content-center">
            <?php
            $team = [
                ['name' => 'Ahmad Fauzi, S.Kom., M.T.', 'role' => 'Founder & Lead Developer', 'desc' => 'Spesialis pengembangan OJS dengan pengalaman 7+ tahun. Aktif berkontribusi di komunitas PKP Indonesia.', 'icon' => 'fas fa-user-tie'],
                ['name' => 'Dr. Rizky Amelia, M.Pd.',   'role' => 'Academic & Indexing Specialist', 'desc' => 'Mantan editor jurnal SINTA 2, ahli strategi akreditasi dan indexing jurnal ilmiah nasional & internasional.', 'icon' => 'fas fa-user-graduate'],
                ['name' => 'Budi Santoso, S.T.',         'role' => 'Server & DevOps Engineer',    'desc' => 'Bertanggung jawab atas infrastruktur server, keamanan sistem, dan performa website jurnal klien kami.', 'icon' => 'fas fa-user-cog'],
                ['name' => 'Siti Rahayu, S.Pd.',         'role' => 'Training & Support Specialist', 'desc' => 'Fasilitator pelatihan OJS berpengalaman yang telah melatih ratusan editor dan administrator jurnal di seluruh Indonesia.', 'icon' => 'fas fa-user-friends'],
            ];
            foreach ($team as $i => $member):
            ?>
            <div class="col-sm-6 col-lg-3">
                <div class="team-card fade-in-up" style="animation-delay:<?= $i * 0.1 ?>s">
                    <div class="team-avatar">
                        <i class="<?= $member['icon'] ?>"></i>
                    </div>
                    <div class="team-info">
                        <h5 class="team-name"><?= $member['name'] ?></h5>
                        <span class="team-role"><?= $member['role'] ?></span>
                        <p class="team-desc"><?= $member['desc'] ?></p>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Testimonial -->
<section class="section-padding bg-white">
    <div class="container">
        <div class="section-header text-center fade-in-up mb-5">
            <span class="section-badge">Apa Kata Klien Kami</span>
            <h2 class="section-title">Testimoni</h2>
        </div>
        <div class="row g-4">
            <?php
            $testimonials = [
                ['text' => '"Proses instalasi dan konfigurasi OJS sangat cepat dan profesional. Tim OJS Developer Indonesia sangat responsif dalam menjawab pertanyaan-pertanyaan teknis kami. Dalam 2 minggu jurnal kami sudah online dan siap menerima submisi."', 'name' => 'Prof. Dr. Hendra Kusuma, M.Si.', 'role' => 'Ketua Redaksi – Jurnal Ilmu Pendidikan Universitas Nusantara'],
                ['text' => '"Kami awalnya ragu apakah bisa belajar menggunakan OJS, tapi pelatihan yang diberikan sangat terstruktur dan mudah dipahami. Sekarang seluruh tim editorial kami sudah bisa mengelola jurnal secara mandiri."', 'name' => 'Dra. Nur Fitria, M.Pd.', 'role' => 'Managing Editor – Jurnal Pendidikan Bahasa dan Sastra'],
                ['text' => '"OJS Developer Indonesia membantu kami menaikkan jurnal dari SINTA 4 ke SINTA 3. Mereka memandu kami dalam perbaikan teknis, metadata, dan proses pengajuan ulang ke ARJUNA. Sangat direkomendasikan!"', 'name' => 'Dr. Agus Setiawan, M.T.', 'role' => 'Editor in Chief – Jurnal Teknik dan Ilmu Komputer'],
            ];
            foreach ($testimonials as $i => $t):
            ?>
            <div class="col-md-4">
                <div class="testimonial-card fade-in-up" style="animation-delay:<?= $i * 0.1 ?>s">
                    <div class="testimonial-quote-icon"><i class="fas fa-quote-left"></i></div>
                    <p class="testimonial-text"><?= $t['text'] ?></p>
                    <div class="testimonial-author">
                        <div class="testimonial-avatar"><i class="fas fa-user-circle"></i></div>
                        <div>
                            <div class="testimonial-name"><?= $t['name'] ?></div>
                            <div class="testimonial-role"><?= $t['role'] ?></div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
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
            <h2 class="cta-title">Bergabunglah bersama 150+ Jurnal yang Telah Kami Bantu</h2>
            <p class="cta-subtitle">Mulailah perjalanan transformasi digital jurnal Anda bersama tim ahli kami.</p>
            <div class="cta-actions">
                <a href="<?= $siteUrl ?>/konsultasi" class="btn btn-accent btn-lg">
                    <i class="fas fa-comments me-2"></i>Konsultasi Gratis
                </a>
                <a href="<?= $siteUrl ?>/portofolio" class="btn btn-outline-light btn-lg">
                    <i class="fas fa-images me-2"></i>Lihat Portofolio
                </a>
            </div>
        </div>
    </div>
</section>
