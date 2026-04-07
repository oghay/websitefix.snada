<?php
/**
 * Home Page
 */
$pageTitle = getSetting('site_name', 'OJS Developer Indonesia') . ' – Jasa Pembuatan Website Jurnal OJS Profesional';
$siteUrl   = defined('SITE_URL') ? SITE_URL : '';

// Load featured portfolio
$featured = fetchAll("SELECT * FROM portfolio WHERE status='published' ORDER BY is_featured DESC, created_at DESC LIMIT 6");

// Load latest blog posts
$latestPosts = fetchAll("SELECT * FROM blog_posts WHERE status='published' ORDER BY created_at DESC LIMIT 3");
?>

<!-- ══════════════════════════════════════════
     HERO SECTION
══════════════════════════════════════════ -->
<section class="hero-section" aria-label="Hero">
    <!-- Decorative shapes -->
    <div class="hero-shapes" aria-hidden="true">
        <div class="hero-shape hero-shape-1"></div>
        <div class="hero-shape hero-shape-2"></div>
        <div class="hero-shape hero-shape-3"></div>
        <div class="hero-shape hero-shape-4"></div>
    </div>

    <div class="container hero-content">
        <div class="row align-items-center min-vh-75">
            <div class="col-lg-7 col-xl-6">
                <div class="fade-in-up">
                    <span class="hero-badge">
                        <i class="fas fa-star-of-david me-2"></i>Terpercaya sejak 2019
                    </span>
                    <h1 class="hero-title">
                        Jasa Pembuatan<br>
                        <span class="text-gradient">Website Jurnal OJS</span><br>
                        Profesional
                    </h1>
                    <p class="hero-subtitle">
                        Kami membantu perguruan tinggi, lembaga penelitian, dan organisasi akademik di seluruh Indonesia membangun website jurnal ilmiah yang modern, terindeks, dan mudah dikelola menggunakan Open Journal Systems (OJS).
                    </p>
                    <div class="hero-actions">
                        <a href="<?= $siteUrl ?>/konsultasi" class="btn btn-accent btn-lg">
                            <i class="fas fa-comments me-2"></i>Konsultasi Gratis
                        </a>
                        <a href="<?= $siteUrl ?>/portofolio" class="btn btn-outline-light btn-lg">
                            <i class="fas fa-images me-2"></i>Lihat Portofolio
                        </a>
                    </div>
                    <div class="hero-trust mt-4">
                        <span class="hero-trust-item">
                            <i class="fas fa-shield-alt text-accent me-1"></i> Keamanan Terjamin
                        </span>
                        <span class="hero-trust-item">
                            <i class="fas fa-headset text-accent me-1"></i> Support 24/7
                        </span>
                        <span class="hero-trust-item">
                            <i class="fas fa-certificate text-accent me-1"></i> Bergaransi
                        </span>
                    </div>
                </div>
            </div>
            <div class="col-lg-5 col-xl-6 d-none d-lg-block">
                <div class="hero-illustration fade-in-right">
                    <div class="browser-mockup">
                        <div class="browser-bar">
                            <span class="browser-dot dot-red"></span>
                            <span class="browser-dot dot-yellow"></span>
                            <span class="browser-dot dot-green"></span>
                            <div class="browser-url-bar">
                                <i class="fas fa-lock me-1" style="font-size:0.65rem;"></i>
                                jurnal.universitasanda.ac.id
                            </div>
                        </div>
                        <div class="browser-content">
                            <div class="mock-header"></div>
                            <div class="mock-hero"></div>
                            <div class="mock-cards">
                                <div class="mock-card"></div>
                                <div class="mock-card"></div>
                                <div class="mock-card"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ══════════════════════════════════════════
     STATS COUNTER
══════════════════════════════════════════ -->
<section class="stats-section">
    <div class="container">
        <div class="row g-3 g-md-4">
            <div class="col-6 col-md-3">
                <div class="stat-card fade-in-up">
                    <div class="stat-number" data-count="150">0</div>
                    <div class="stat-suffix">+</div>
                    <div class="stat-label">Jurnal Dibangun</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-card fade-in-up" style="animation-delay:0.1s">
                    <div class="stat-number" data-count="50">0</div>
                    <div class="stat-suffix">+</div>
                    <div class="stat-label">Institusi Dilayani</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-card fade-in-up" style="animation-delay:0.2s">
                    <div class="stat-number" data-count="99">0</div>
                    <div class="stat-suffix">%</div>
                    <div class="stat-label">Tingkat Kepuasan</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-card fade-in-up" style="animation-delay:0.3s">
                    <div class="stat-number" data-count="5">0</div>
                    <div class="stat-suffix">+</div>
                    <div class="stat-label">Tahun Pengalaman</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ══════════════════════════════════════════
     FEATURES / LAYANAN
══════════════════════════════════════════ -->
<section class="section-padding bg-white">
    <div class="container">
        <div class="section-header text-center fade-in-up">
            <span class="section-badge">Apa yang Kami Tawarkan</span>
            <h2 class="section-title">Layanan Lengkap untuk Jurnal Anda</h2>
            <p class="section-subtitle">
                Dari pemasangan awal hingga pengembangan lanjutan, kami menyediakan solusi end-to-end untuk kebutuhan jurnal ilmiah digital Anda.
            </p>
        </div>
        <div class="row g-4 mt-2">
            <?php
            $features = [
                ['icon' => 'fas fa-server',        'title' => 'Instalasi OJS',        'desc' => 'Pemasangan dan konfigurasi Open Journal Systems versi terbaru di server Anda dengan pengaturan keamanan dan performa optimal.', 'color' => 'primary'],
                ['icon' => 'fas fa-paint-brush',   'title' => 'Kustomisasi Tema',     'desc' => 'Desain tampilan jurnal yang mencerminkan identitas institusi Anda — logo, warna, tipografi, dan tata letak yang profesional.', 'color' => 'secondary'],
                ['icon' => 'fas fa-exchange-alt',  'title' => 'Migrasi Jurnal',       'desc' => 'Pindahkan seluruh data jurnal Anda — artikel, pengguna, edisi — dari platform lama ke OJS tanpa kehilangan satu pun data.', 'color' => 'accent'],
                ['icon' => 'fas fa-chalkboard-teacher', 'title' => 'Pelatihan OJS',  'desc' => 'Workshop intensif untuk editor, reviewer, dan administrator jurnal agar dapat mengelola jurnal secara mandiri dan efisien.', 'color' => 'primary'],
                ['icon' => 'fas fa-tools',         'title' => 'Maintenance',          'desc' => 'Pemeliharaan rutin, pembaruan sistem, backup berkala, dan penanganan cepat jika terjadi gangguan teknis pada jurnal Anda.', 'color' => 'secondary'],
                ['icon' => 'fas fa-search',        'title' => 'Indexing & SEO',       'desc' => 'Optimasi jurnal agar terindeks di SINTA, DOAJ, Google Scholar, Crossref, dan mesin pencari utama untuk meningkatkan visibilitas artikel.', 'color' => 'accent'],
            ];
            foreach ($features as $i => $f):
            ?>
            <div class="col-md-6 col-lg-4">
                <div class="feature-card fade-in-up" style="animation-delay:<?= $i * 0.08 ?>s">
                    <div class="feature-icon-wrap feature-icon-<?= $f['color'] ?>">
                        <i class="<?= $f['icon'] ?>"></i>
                    </div>
                    <h3 class="feature-title"><?= $f['title'] ?></h3>
                    <p class="feature-desc"><?= $f['desc'] ?></p>
                    <a href="<?= $siteUrl ?>/layanan" class="feature-link">
                        Selengkapnya <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="text-center mt-5 fade-in-up">
            <a href="<?= $siteUrl ?>/layanan" class="btn btn-primary btn-lg">
                <i class="fas fa-list me-2"></i>Lihat Semua Layanan
            </a>
        </div>
    </div>
</section>

<!-- ══════════════════════════════════════════
     FEATURED PORTFOLIO
══════════════════════════════════════════ -->
<section class="section-padding" style="background: var(--bg-light);">
    <div class="container">
        <div class="section-header text-center fade-in-up">
            <span class="section-badge">Hasil Karya Kami</span>
            <h2 class="section-title">Portofolio Terpilih</h2>
            <p class="section-subtitle">
                Lebih dari 150 jurnal ilmiah telah kami bangun dan kelola bersama institusi-institusi akademik ternama di Indonesia.
            </p>
        </div>

        <?php if (!empty($featured)): ?>
        <div class="row g-4 mt-2">
            <?php foreach ($featured as $i => $item): ?>
            <div class="col-md-6 col-lg-4">
                <div class="portfolio-card fade-in-up" style="animation-delay:<?= $i * 0.08 ?>s">
                    <div class="portfolio-card-img">
                        <?php if (!empty($item['image'])): ?>
                            <img src="<?= $siteUrl ?>/assets/uploads/portfolio/<?= htmlspecialchars($item['image']) ?>"
                                 alt="<?= htmlspecialchars($item['title']) ?>"
                                 loading="lazy">
                        <?php else: ?>
                            <div class="portfolio-card-placeholder">
                                <i class="fas fa-newspaper"></i>
                            </div>
                        <?php endif; ?>
                        <div class="portfolio-card-overlay">
                            <a href="<?= $siteUrl ?>/portofolio/<?= htmlspecialchars($item['slug']) ?>"
                               class="btn btn-light btn-sm">
                                <i class="fas fa-eye me-1"></i> Lihat Detail
                            </a>
                        </div>
                        <?php if ($item['is_featured']): ?>
                        <span class="portfolio-featured-badge">
                            <i class="fas fa-star me-1"></i>Unggulan
                        </span>
                        <?php endif; ?>
                    </div>
                    <div class="portfolio-card-body">
                        <span class="category-badge category-<?= htmlspecialchars($item['category']) ?>">
                            <?= ucfirst(htmlspecialchars($item['category'])) ?>
                        </span>
                        <h4 class="portfolio-card-title">
                            <a href="<?= $siteUrl ?>/portofolio/<?= htmlspecialchars($item['slug']) ?>">
                                <?= htmlspecialchars($item['title']) ?>
                            </a>
                        </h4>
                        <?php if (!empty($item['client_institution'])): ?>
                        <p class="portfolio-institution">
                            <i class="fas fa-university me-1 text-muted"></i>
                            <?= htmlspecialchars($item['client_institution']) ?>
                        </p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <!-- Placeholder cards when DB is empty -->
        <div class="row g-4 mt-2">
            <?php
            $placeholders = [
                ['title' => 'Jurnal Pendidikan Indonesia', 'inst' => 'Universitas Pendidikan Indonesia', 'cat' => 'jurnal'],
                ['title' => 'Jurnal Kesehatan Masyarakat', 'inst' => 'Universitas Indonesia', 'cat' => 'jurnal'],
                ['title' => 'Prosiding Konferensi Nasional', 'inst' => 'Institut Teknologi Bandung', 'cat' => 'konferensi'],
                ['title' => 'Jurnal Teknik Informatika', 'inst' => 'Universitas Gadjah Mada', 'cat' => 'jurnal'],
                ['title' => 'Jurnal Ekonomi & Bisnis', 'inst' => 'Universitas Airlangga', 'cat' => 'jurnal'],
                ['title' => 'Repositori Riset Nasional', 'inst' => 'LIPI Indonesia', 'cat' => 'repositori'],
            ];
            foreach ($placeholders as $i => $p):
            ?>
            <div class="col-md-6 col-lg-4">
                <div class="portfolio-card fade-in-up" style="animation-delay:<?= $i * 0.08 ?>s">
                    <div class="portfolio-card-img">
                        <div class="portfolio-card-placeholder">
                            <i class="fas fa-newspaper"></i>
                        </div>
                    </div>
                    <div class="portfolio-card-body">
                        <span class="category-badge category-<?= $p['cat'] ?>">
                            <?= ucfirst($p['cat']) ?>
                        </span>
                        <h4 class="portfolio-card-title"><?= $p['title'] ?></h4>
                        <p class="portfolio-institution">
                            <i class="fas fa-university me-1 text-muted"></i><?= $p['inst'] ?>
                        </p>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <div class="text-center mt-5 fade-in-up">
            <a href="<?= $siteUrl ?>/portofolio" class="btn btn-outline-primary btn-lg">
                <i class="fas fa-images me-2"></i>Lihat Semua Portofolio
            </a>
        </div>
    </div>
</section>

<!-- ══════════════════════════════════════════
     WHY CHOOSE US
══════════════════════════════════════════ -->
<section class="section-padding bg-white">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-6 fade-in-up">
                <span class="section-badge">Keunggulan Kami</span>
                <h2 class="section-title">Mengapa Memilih OJS Developer Indonesia?</h2>
                <p class="text-muted mb-4">
                    Kami bukan sekadar penyedia jasa teknis — kami adalah mitra strategis yang memahami kebutuhan unik jurnal ilmiah akademik Indonesia.
                </p>
                <div class="why-list">
                    <?php
                    $whyItems = [
                        ['icon' => 'fas fa-award',          'title' => 'Berpengalaman & Terspesialisasi',    'desc' => 'Lebih dari 5 tahun fokus eksklusif pada ekosistem OJS, bukan jasa pembuatan website umum.'],
                        ['icon' => 'fas fa-handshake',      'title' => 'Pendampingan Jangka Panjang',        'desc' => 'Kami tetap ada setelah website selesai — konsultasi, update, dan support tanpa batas waktu.'],
                        ['icon' => 'fas fa-graduation-cap', 'title' => 'Memahami Dunia Akademik',            'desc' => 'Tim kami berlatar belakang akademik dan memahami alur kerja editorial jurnal ilmiah.'],
                        ['icon' => 'fas fa-bolt',           'title' => 'Pengerjaan Cepat & Tepat Waktu',     'desc' => 'Estimasi waktu pengerjaan yang realistis dengan jaminan penyelesaian sesuai kesepakatan.'],
                    ];
                    foreach ($whyItems as $item):
                    ?>
                    <div class="why-item">
                        <div class="why-icon">
                            <i class="<?= $item['icon'] ?>"></i>
                        </div>
                        <div class="why-text">
                            <h5><?= $item['title'] ?></h5>
                            <p><?= $item['desc'] ?></p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="col-lg-6 fade-in-right">
                <div class="testimonial-card">
                    <div class="testimonial-quote-icon"><i class="fas fa-quote-left"></i></div>
                    <p class="testimonial-text">
                        "OJS Developer Indonesia membantu kami memigrasikan seluruh jurnal lama ke OJS versi terbaru dalam waktu kurang dari 2 minggu. Tidak ada satupun artikel yang hilang, dan tampilannya jauh lebih profesional dari sebelumnya. Pelatihan yang diberikan juga sangat lengkap dan mudah dipahami oleh tim editorial kami."
                    </p>
                    <div class="testimonial-author">
                        <div class="testimonial-avatar">
                            <i class="fas fa-user-circle"></i>
                        </div>
                        <div>
                            <div class="testimonial-name">Dr. Sari Rahmawati, M.Pd.</div>
                            <div class="testimonial-role">Ketua Redaksi – Jurnal Pendidikan Nusantara</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ══════════════════════════════════════════
     LATEST BLOG POSTS
══════════════════════════════════════════ -->
<?php if (!empty($latestPosts)): ?>
<section class="section-padding" style="background: var(--bg-light);">
    <div class="container">
        <div class="section-header text-center fade-in-up">
            <span class="section-badge">Artikel Terbaru</span>
            <h2 class="section-title">Blog & Panduan OJS</h2>
            <p class="section-subtitle">Artikel, tutorial, dan tips terkini seputar pengelolaan jurnal ilmiah digital.</p>
        </div>
        <div class="row g-4 mt-2">
            <?php foreach ($latestPosts as $i => $post): ?>
            <div class="col-md-4">
                <article class="blog-card fade-in-up" style="animation-delay:<?= $i * 0.1 ?>s">
                    <div class="blog-card-img">
                        <?php if (!empty($post['image'])): ?>
                            <img src="<?= $siteUrl ?>/assets/uploads/blog/<?= htmlspecialchars($post['image']) ?>"
                                 alt="<?= htmlspecialchars($post['title']) ?>" loading="lazy">
                        <?php else: ?>
                            <div class="blog-card-placeholder">
                                <i class="fas fa-pen-nib"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="blog-card-body">
                        <div class="blog-card-meta">
                            <span><i class="fas fa-calendar-alt me-1"></i><?= formatDate($post['created_at']) ?></span>
                            <span><i class="fas fa-user me-1"></i><?= htmlspecialchars($post['author']) ?></span>
                        </div>
                        <h4 class="blog-card-title">
                            <a href="<?= $siteUrl ?>/blog/<?= htmlspecialchars($post['slug']) ?>">
                                <?= htmlspecialchars($post['title']) ?>
                            </a>
                        </h4>
                        <p class="blog-card-excerpt"><?= htmlspecialchars(truncate($post['excerpt'] ?: $post['content'], 120)) ?></p>
                        <a href="<?= $siteUrl ?>/blog/<?= htmlspecialchars($post['slug']) ?>" class="blog-read-more">
                            Baca Selengkapnya <i class="fas fa-arrow-right ms-1"></i>
                        </a>
                    </div>
                </article>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="text-center mt-5 fade-in-up">
            <a href="<?= $siteUrl ?>/blog" class="btn btn-outline-primary btn-lg">
                <i class="fas fa-book-open me-2"></i>Lihat Semua Artikel
            </a>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ══════════════════════════════════════════
     CTA SECTION
══════════════════════════════════════════ -->
<section class="cta-section">
    <div class="cta-shapes" aria-hidden="true">
        <div class="cta-shape cta-shape-1"></div>
        <div class="cta-shape cta-shape-2"></div>
    </div>
    <div class="container text-center">
        <div class="cta-content fade-in-up">
            <h2 class="cta-title">Siap Membangun Jurnal Online Anda?</h2>
            <p class="cta-subtitle">
                Dapatkan konsultasi gratis dan penawaran terbaik untuk kebutuhan jurnal institusi Anda. Tim ahli kami siap membantu Anda mulai hari ini.
            </p>
            <div class="cta-actions">
                <a href="<?= $siteUrl ?>/konsultasi" class="btn btn-accent btn-lg">
                    <i class="fas fa-comments me-2"></i>Konsultasi Gratis Sekarang
                </a>
                <a href="<?= $siteUrl ?>/harga" class="btn btn-outline-light btn-lg">
                    <i class="fas fa-tags me-2"></i>Lihat Paket Harga
                </a>
            </div>
        </div>
    </div>
</section>
