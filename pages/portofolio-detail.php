<?php
/**
 * Portfolio Detail Page
 */
$siteUrl = defined('SITE_URL') ? SITE_URL : '';
$slug    = sanitize($_GET['slug'] ?? '');

if (empty($slug)) {
    redirect($siteUrl . '/portofolio');
}

$item = fetch("SELECT * FROM portfolio WHERE slug = ? AND status = 'published'", [$slug]);

if (!$item) {
    http_response_code(404);
    echo '<div class="container py-5 text-center"><div class="empty-state"><i class="fas fa-search fa-4x text-muted mb-3"></i><h3>Portofolio Tidak Ditemukan</h3><p class="text-muted">Portofolio yang Anda cari tidak tersedia atau telah dihapus.</p><a href="' . $siteUrl . '/portofolio" class="btn btn-primary mt-2">Lihat Semua Portofolio</a></div></div>';
    return;
}

$pageTitle = htmlspecialchars($item['title']) . ' – Portofolio – ' . getSetting('site_name', 'OJS Developer Indonesia');
$metaDesc  = truncate($item['description'], 160);

// Related items
$related = fetchAll(
    "SELECT * FROM portfolio WHERE status='published' AND category = ? AND id != ? ORDER BY is_featured DESC, created_at DESC LIMIT 3",
    [$item['category'], $item['id']]
);
?>

<!-- Page Hero -->
<div class="page-hero">
    <div class="container">
        <div class="page-hero-content text-center fade-in-up">
            <span class="category-badge category-<?= htmlspecialchars($item['category']) ?> mb-3 d-inline-block">
                <?= ucfirst(htmlspecialchars($item['category'])) ?>
            </span>
            <h1 class="page-hero-title"><?= htmlspecialchars($item['title']) ?></h1>
            <?php if (!empty($item['client_institution'])): ?>
            <p class="page-hero-subtitle">
                <i class="fas fa-university me-2"></i><?= htmlspecialchars($item['client_institution']) ?>
            </p>
            <?php endif; ?>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb justify-content-center">
                    <li class="breadcrumb-item"><a href="<?= $siteUrl ?>/">Beranda</a></li>
                    <li class="breadcrumb-item"><a href="<?= $siteUrl ?>/portofolio">Portofolio</a></li>
                    <li class="breadcrumb-item active"><?= htmlspecialchars($item['title']) ?></li>
                </ol>
            </nav>
        </div>
    </div>
</div>

<section class="section-padding bg-white">
    <div class="container">
        <div class="row g-5">
            <!-- Main Content -->
            <div class="col-lg-8">
                <!-- Main Image -->
                <?php if (!empty($item['image'])): ?>
                <div class="portfolio-detail-image mb-4 fade-in-up">
                    <img src="<?= $siteUrl ?>/assets/uploads/portfolio/<?= htmlspecialchars($item['image']) ?>"
                         alt="<?= htmlspecialchars($item['title']) ?>"
                         class="img-fluid rounded-3 shadow-md w-100"
                         style="max-height:460px; object-fit:cover;">
                </div>
                <?php else: ?>
                <div class="portfolio-detail-placeholder mb-4 fade-in-up rounded-3">
                    <i class="fas fa-newspaper fa-5x text-muted"></i>
                </div>
                <?php endif; ?>

                <!-- Description -->
                <div class="portfolio-detail-content fade-in-up">
                    <h2 class="h4 fw-700 mb-3">Tentang Proyek</h2>
                    <div class="article-content">
                        <?php if (!empty($item['description'])): ?>
                            <?= nl2br(htmlspecialchars($item['description'])) ?>
                        <?php else: ?>
                            <p class="text-muted">Deskripsi proyek belum tersedia.</p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Back Link -->
                <div class="mt-5 fade-in-up">
                    <a href="<?= $siteUrl ?>/portofolio" class="btn btn-outline-primary">
                        <i class="fas fa-arrow-left me-2"></i>Kembali ke Portofolio
                    </a>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <div class="sidebar-card fade-in-up">
                    <h5 class="sidebar-card-title">Informasi Proyek</h5>
                    <ul class="project-info-list">
                        <?php if (!empty($item['client_name'])): ?>
                        <li>
                            <span class="project-info-label"><i class="fas fa-user me-2"></i>Klien</span>
                            <span class="project-info-value"><?= htmlspecialchars($item['client_name']) ?></span>
                        </li>
                        <?php endif; ?>
                        <?php if (!empty($item['client_institution'])): ?>
                        <li>
                            <span class="project-info-label"><i class="fas fa-university me-2"></i>Institusi</span>
                            <span class="project-info-value"><?= htmlspecialchars($item['client_institution']) ?></span>
                        </li>
                        <?php endif; ?>
                        <li>
                            <span class="project-info-label"><i class="fas fa-tag me-2"></i>Kategori</span>
                            <span class="category-badge category-<?= htmlspecialchars($item['category']) ?>">
                                <?= ucfirst(htmlspecialchars($item['category'])) ?>
                            </span>
                        </li>
                        <li>
                            <span class="project-info-label"><i class="fas fa-calendar me-2"></i>Tanggal</span>
                            <span class="project-info-value"><?= formatDate($item['created_at']) ?></span>
                        </li>
                        <?php if ($item['is_featured']): ?>
                        <li>
                            <span class="project-info-label"><i class="fas fa-star me-2"></i>Status</span>
                            <span class="badge bg-warning text-dark"><i class="fas fa-star me-1"></i>Proyek Unggulan</span>
                        </li>
                        <?php endif; ?>
                    </ul>
                    <?php if (!empty($item['website_url'])): ?>
                    <div class="mt-3">
                        <a href="<?= htmlspecialchars($item['website_url']) ?>" target="_blank" rel="noopener"
                           class="btn btn-primary w-100">
                            <i class="fas fa-external-link-alt me-2"></i>Kunjungi Website Jurnal
                        </a>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- CTA Card -->
                <div class="sidebar-cta-card mt-4 fade-in-up">
                    <i class="fas fa-comments fa-2x mb-2"></i>
                    <h5>Ingin Jurnal Serupa?</h5>
                    <p>Konsultasikan kebutuhan jurnal Anda dengan tim kami secara gratis.</p>
                    <a href="<?= $siteUrl ?>/konsultasi" class="btn btn-accent w-100">
                        Mulai Konsultasi
                    </a>
                </div>
            </div>
        </div>

        <!-- Related Portfolio -->
        <?php if (!empty($related)): ?>
        <div class="mt-6">
            <h3 class="h4 fw-700 mb-4">Portofolio Terkait</h3>
            <div class="row g-4">
                <?php foreach ($related as $rel): ?>
                <div class="col-md-4">
                    <div class="portfolio-card">
                        <div class="portfolio-card-img">
                            <?php if (!empty($rel['image'])): ?>
                                <img src="<?= $siteUrl ?>/assets/uploads/portfolio/<?= htmlspecialchars($rel['image']) ?>"
                                     alt="<?= htmlspecialchars($rel['title']) ?>" loading="lazy">
                            <?php else: ?>
                                <div class="portfolio-card-placeholder"><i class="fas fa-newspaper"></i></div>
                            <?php endif; ?>
                            <div class="portfolio-card-overlay">
                                <a href="<?= $siteUrl ?>/portofolio/<?= htmlspecialchars($rel['slug']) ?>"
                                   class="btn btn-light btn-sm"><i class="fas fa-eye me-1"></i>Lihat Detail</a>
                            </div>
                        </div>
                        <div class="portfolio-card-body">
                            <span class="category-badge category-<?= htmlspecialchars($rel['category']) ?>">
                                <?= ucfirst(htmlspecialchars($rel['category'])) ?>
                            </span>
                            <h5 class="portfolio-card-title">
                                <a href="<?= $siteUrl ?>/portofolio/<?= htmlspecialchars($rel['slug']) ?>">
                                    <?= htmlspecialchars($rel['title']) ?>
                                </a>
                            </h5>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</section>
