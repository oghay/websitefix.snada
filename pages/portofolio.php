<?php
/**
 * Portfolio Page
 */
$pageTitle = 'Portofolio – ' . getSetting('site_name', 'OJS Developer Indonesia');
$siteUrl   = defined('SITE_URL') ? SITE_URL : '';

// Pagination
$perPage     = 9;
$currentPage = max(1, (int)($_GET['p'] ?? 1));
$filterCat   = in_array($_GET['cat'] ?? '', ['jurnal','konferensi','repositori','lainnya']) ? $_GET['cat'] : '';

// Build count query
if ($filterCat) {
    $total = (int) fetch("SELECT COUNT(*) as c FROM portfolio WHERE status='published' AND category = ?", [$filterCat])['c'];
} else {
    $total = (int) fetch("SELECT COUNT(*) as c FROM portfolio WHERE status='published'")['c'];
}

$pagination = getPagination($total, $perPage, $currentPage);

// Fetch items — inline LIMIT/OFFSET (safe: values are already cast to int)
$limit  = (int) $pagination['per_page'];
$offset = (int) $pagination['offset'];
if ($filterCat) {
    $items = fetchAll(
        "SELECT * FROM portfolio WHERE status='published' AND category = ? ORDER BY is_featured DESC, created_at DESC LIMIT {$limit} OFFSET {$offset}",
        [$filterCat]
    );
} else {
    $items = fetchAll(
        "SELECT * FROM portfolio WHERE status='published' ORDER BY is_featured DESC, created_at DESC LIMIT {$limit} OFFSET {$offset}"
    );
}
?>

<!-- Page Hero -->
<div class="page-hero">
    <div class="container">
        <div class="page-hero-content text-center fade-in-up">
            <h1 class="page-hero-title">Portofolio Kami</h1>
            <p class="page-hero-subtitle">Jurnal-jurnal ilmiah yang telah kami bangun dan kembangkan bersama institusi akademik terkemuka di Indonesia</p>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb justify-content-center">
                    <li class="breadcrumb-item"><a href="<?= $siteUrl ?>/">Beranda</a></li>
                    <li class="breadcrumb-item active">Portofolio</li>
                </ol>
            </nav>
        </div>
    </div>
</div>

<section class="section-padding bg-white">
    <div class="container">

        <!-- Filter Buttons -->
        <div class="portfolio-filter-bar fade-in-up mb-5">
            <div class="d-flex flex-wrap justify-content-center gap-2">
                <a href="<?= $siteUrl ?>/portofolio"
                   class="btn btn-filter <?= $filterCat === '' ? 'active' : '' ?>">
                    <i class="fas fa-th me-1"></i> Semua
                </a>
                <a href="<?= $siteUrl ?>/portofolio?cat=jurnal"
                   class="btn btn-filter <?= $filterCat === 'jurnal' ? 'active' : '' ?>">
                    <i class="fas fa-newspaper me-1"></i> Jurnal
                </a>
                <a href="<?= $siteUrl ?>/portofolio?cat=konferensi"
                   class="btn btn-filter <?= $filterCat === 'konferensi' ? 'active' : '' ?>">
                    <i class="fas fa-users me-1"></i> Konferensi
                </a>
                <a href="<?= $siteUrl ?>/portofolio?cat=repositori"
                   class="btn btn-filter <?= $filterCat === 'repositori' ? 'active' : '' ?>">
                    <i class="fas fa-database me-1"></i> Repositori
                </a>
                <a href="<?= $siteUrl ?>/portofolio?cat=lainnya"
                   class="btn btn-filter <?= $filterCat === 'lainnya' ? 'active' : '' ?>">
                    <i class="fas fa-ellipsis-h me-1"></i> Lainnya
                </a>
            </div>
        </div>

        <!-- Portfolio Grid -->
        <?php if (!empty($items)): ?>
        <div class="row g-4" id="portfolioGrid">
            <?php foreach ($items as $i => $item): ?>
            <div class="col-md-6 col-lg-4 portfolio-item" data-category="<?= htmlspecialchars($item['category']) ?>">
                <div class="portfolio-card fade-in-up" style="animation-delay:<?= ($i % 9) * 0.06 ?>s">
                    <div class="portfolio-card-img">
                        <?php if (!empty($item['image'])): ?>
                            <img src="<?= $siteUrl ?>/assets/uploads/portfolio/<?= htmlspecialchars($item['image']) ?>"
                                 alt="<?= htmlspecialchars($item['title']) ?>" loading="lazy">
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
                        <?php if (!empty($item['website_url'])): ?>
                        <a href="<?= htmlspecialchars($item['website_url']) ?>" target="_blank" rel="noopener"
                           class="btn btn-sm btn-outline-secondary mt-1">
                            <i class="fas fa-external-link-alt me-1"></i> Kunjungi Website
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Pagination -->
        <?php if ($pagination['total_pages'] > 1): ?>
        <nav class="mt-5" aria-label="Navigasi halaman portofolio">
            <ul class="pagination justify-content-center">
                <?php if ($pagination['has_prev']): ?>
                <li class="page-item">
                    <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['p' => $pagination['prev']])) ?>">
                        <i class="fas fa-chevron-left"></i>
                    </a>
                </li>
                <?php endif; ?>

                <?php for ($p = 1; $p <= $pagination['total_pages']; $p++): ?>
                <li class="page-item <?= $p === $pagination['current'] ? 'active' : '' ?>">
                    <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['p' => $p])) ?>">
                        <?= $p ?>
                    </a>
                </li>
                <?php endfor; ?>

                <?php if ($pagination['has_next']): ?>
                <li class="page-item">
                    <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['p' => $pagination['next']])) ?>">
                        <i class="fas fa-chevron-right"></i>
                    </a>
                </li>
                <?php endif; ?>
            </ul>
        </nav>
        <?php endif; ?>

        <?php else: ?>
        <div class="empty-state text-center py-5">
            <i class="fas fa-images fa-4x text-muted mb-3"></i>
            <h4>Belum Ada Portofolio</h4>
            <p class="text-muted">
                <?php if ($filterCat): ?>
                    Belum ada portofolio untuk kategori "<?= ucfirst(htmlspecialchars($filterCat)) ?>".
                    <a href="<?= $siteUrl ?>/portofolio">Lihat semua kategori</a>
                <?php else: ?>
                    Portofolio akan segera ditambahkan. Silakan kunjungi kembali nanti.
                <?php endif; ?>
            </p>
        </div>
        <?php endif; ?>

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
            <h2 class="cta-title">Ingin Jurnal Anda Menjadi Bagian dari Portofolio Kami?</h2>
            <p class="cta-subtitle">Hubungi kami dan mulailah perjalanan digitalisasi jurnal Anda bersama tim ahli kami.</p>
            <div class="cta-actions">
                <a href="<?= $siteUrl ?>/konsultasi" class="btn btn-accent btn-lg">
                    <i class="fas fa-comments me-2"></i>Konsultasi Gratis
                </a>
            </div>
        </div>
    </div>
</section>
