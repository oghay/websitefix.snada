<?php
/**
 * Blog Listing Page
 */
$pageTitle = 'Blog & Panduan OJS – ' . getSetting('site_name', 'OJS Developer Indonesia');
$siteUrl   = defined('SITE_URL') ? SITE_URL : '';

$perPage     = 9;
$currentPage = max(1, (int)($_GET['p'] ?? 1));
$search      = sanitize($_GET['q'] ?? '');

// Count
if ($search) {
    $total = (int) fetch(
        "SELECT COUNT(*) as c FROM blog_posts WHERE status='published' AND title LIKE ?",
        ['%' . $search . '%']
    )['c'];
} else {
    $total = (int) fetch("SELECT COUNT(*) as c FROM blog_posts WHERE status='published'")['c'];
}

$pagination = getPagination($total, $perPage, $currentPage);

// Fetch — inline LIMIT/OFFSET (safe: already cast to int)
$limit  = (int) $pagination['per_page'];
$offset = (int) $pagination['offset'];
if ($search) {
    $posts = fetchAll(
        "SELECT * FROM blog_posts WHERE status='published' AND title LIKE ? ORDER BY created_at DESC LIMIT {$limit} OFFSET {$offset}",
        ['%' . $search . '%']
    );
} else {
    $posts = fetchAll(
        "SELECT * FROM blog_posts WHERE status='published' ORDER BY created_at DESC LIMIT {$limit} OFFSET {$offset}"
    );
}
?>

<!-- Page Hero -->
<div class="page-hero">
    <div class="container">
        <div class="page-hero-content text-center fade-in-up">
            <h1 class="page-hero-title">Blog & Panduan OJS</h1>
            <p class="page-hero-subtitle">Artikel, tutorial, tips, dan berita terkini seputar Open Journal Systems dan manajemen jurnal ilmiah</p>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb justify-content-center">
                    <li class="breadcrumb-item"><a href="<?= $siteUrl ?>/">Beranda</a></li>
                    <li class="breadcrumb-item active">Blog</li>
                </ol>
            </nav>
        </div>
    </div>
</div>

<section class="section-padding bg-white">
    <div class="container">

        <!-- Search Bar -->
        <div class="blog-search-bar fade-in-up mb-5">
            <form method="GET" action="<?= $siteUrl ?>/blog" class="d-flex justify-content-center">
                <div class="input-group" style="max-width:500px;">
                    <input type="text" name="q" class="form-control form-control-lg"
                           placeholder="Cari artikel..."
                           value="<?= htmlspecialchars($search) ?>"
                           aria-label="Cari artikel blog">
                    <button class="btn btn-primary" type="submit">
                        <i class="fas fa-search"></i>
                    </button>
                    <?php if ($search): ?>
                    <a href="<?= $siteUrl ?>/blog" class="btn btn-outline-secondary" title="Hapus pencarian">
                        <i class="fas fa-times"></i>
                    </a>
                    <?php endif; ?>
                </div>
            </form>
            <?php if ($search): ?>
            <p class="text-center text-muted mt-2">
                Menampilkan <?= $total ?> hasil untuk: <strong>"<?= htmlspecialchars($search) ?>"</strong>
            </p>
            <?php endif; ?>
        </div>

        <!-- Posts Grid -->
        <?php if (!empty($posts)): ?>
        <div class="row g-4">
            <?php foreach ($posts as $i => $post): ?>
            <div class="col-md-6 col-lg-4">
                <article class="blog-card fade-in-up" style="animation-delay:<?= ($i % 9) * 0.06 ?>s">
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
                            <span><i class="fas fa-eye me-1"></i><?= number_format($post['views']) ?></span>
                        </div>
                        <h2 class="blog-card-title h5">
                            <a href="<?= $siteUrl ?>/blog/<?= htmlspecialchars($post['slug']) ?>">
                                <?= htmlspecialchars($post['title']) ?>
                            </a>
                        </h2>
                        <p class="blog-card-excerpt">
                            <?= htmlspecialchars(truncate($post['excerpt'] ?: $post['content'], 130)) ?>
                        </p>
                        <a href="<?= $siteUrl ?>/blog/<?= htmlspecialchars($post['slug']) ?>" class="blog-read-more">
                            Baca Selengkapnya <i class="fas fa-arrow-right ms-1"></i>
                        </a>
                    </div>
                </article>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Pagination -->
        <?php if ($pagination['total_pages'] > 1): ?>
        <nav class="mt-5" aria-label="Navigasi halaman blog">
            <ul class="pagination justify-content-center">
                <?php if ($pagination['has_prev']): ?>
                <li class="page-item">
                    <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['p' => $pagination['prev']])) ?>">
                        <i class="fas fa-chevron-left"></i>
                    </a>
                </li>
                <?php endif; ?>

                <?php for ($p = max(1, $pagination['current'] - 2); $p <= min($pagination['total_pages'], $pagination['current'] + 2); $p++): ?>
                <li class="page-item <?= $p === $pagination['current'] ? 'active' : '' ?>">
                    <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['p' => $p])) ?>"><?= $p ?></a>
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
            <i class="fas fa-pen-nib fa-4x text-muted mb-3"></i>
            <h4>
                <?php if ($search): ?>Tidak ada artikel yang cocok<?php else: ?>Belum Ada Artikel<?php endif; ?>
            </h4>
            <p class="text-muted">
                <?php if ($search): ?>
                    Coba kata kunci lain atau <a href="<?= $siteUrl ?>/blog">lihat semua artikel</a>.
                <?php else: ?>
                    Artikel akan segera diterbitkan. Silakan kunjungi kembali nanti.
                <?php endif; ?>
            </p>
        </div>
        <?php endif; ?>

    </div>
</section>
