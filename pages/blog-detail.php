<?php
/**
 * Blog Detail Page
 */
$siteUrl = defined('SITE_URL') ? SITE_URL : '';
$slug    = sanitize($_GET['slug'] ?? '');

if (empty($slug)) {
    redirect($siteUrl . '/blog');
}

$post = fetch("SELECT * FROM blog_posts WHERE slug = ? AND status = 'published'", [$slug]);

if (!$post) {
    http_response_code(404);
    echo '<div class="container py-5 text-center"><div class="empty-state"><i class="fas fa-search fa-4x text-muted mb-3"></i><h3>Artikel Tidak Ditemukan</h3><p class="text-muted">Artikel yang Anda cari tidak tersedia atau telah dihapus.</p><a href="' . $siteUrl . '/blog" class="btn btn-primary mt-2">Lihat Semua Artikel</a></div></div>';
    return;
}

// Increment views
update('blog_posts', ['views' => $post['views'] + 1], 'id = ?', [$post['id']]);

$pageTitle = htmlspecialchars($post['title']) . ' – Blog – ' . getSetting('site_name', 'OJS Developer Indonesia');
$metaDesc  = truncate($post['excerpt'] ?: $post['content'], 160);

// Related posts
$related = fetchAll(
    "SELECT * FROM blog_posts WHERE status='published' AND id != ? ORDER BY created_at DESC LIMIT 3",
    [$post['id']]
);

// Share URL
$shareUrl = urlencode($siteUrl . '/blog/' . $slug);
$shareTitle = urlencode($post['title']);
$waNumber = getSetting('whatsapp_number', '');
?>

<!-- Page Hero -->
<div class="page-hero page-hero-blog">
    <div class="container">
        <div class="page-hero-content text-center fade-in-up" style="max-width:760px; margin:0 auto;">
            <div class="blog-detail-meta mb-3">
                <span><i class="fas fa-calendar-alt me-1"></i><?= formatDate($post['created_at']) ?></span>
                <span class="mx-2">·</span>
                <span><i class="fas fa-user me-1"></i><?= htmlspecialchars($post['author']) ?></span>
                <span class="mx-2">·</span>
                <span><i class="fas fa-eye me-1"></i><?= number_format($post['views'] + 1) ?> kali dibaca</span>
            </div>
            <h1 class="page-hero-title" style="font-size:clamp(1.6rem,4vw,2.5rem);">
                <?= htmlspecialchars($post['title']) ?>
            </h1>
            <?php if (!empty($post['excerpt'])): ?>
            <p class="page-hero-subtitle"><?= htmlspecialchars($post['excerpt']) ?></p>
            <?php endif; ?>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb justify-content-center">
                    <li class="breadcrumb-item"><a href="<?= $siteUrl ?>/">Beranda</a></li>
                    <li class="breadcrumb-item"><a href="<?= $siteUrl ?>/blog">Blog</a></li>
                    <li class="breadcrumb-item active"><?= htmlspecialchars(truncate($post['title'], 40)) ?></li>
                </ol>
            </nav>
        </div>
    </div>
</div>

<section class="section-padding bg-white">
    <div class="container">
        <div class="row g-5 justify-content-center">
            <!-- Main Content -->
            <div class="col-lg-8">

                <!-- Featured Image -->
                <?php if (!empty($post['image'])): ?>
                <div class="mb-5 fade-in-up">
                    <img src="<?= $siteUrl ?>/assets/uploads/blog/<?= htmlspecialchars($post['image']) ?>"
                         alt="<?= htmlspecialchars($post['title']) ?>"
                         class="img-fluid rounded-3 shadow w-100"
                         style="max-height:420px; object-fit:cover;">
                </div>
                <?php endif; ?>

                <!-- Article Content -->
                <div class="article-content fade-in-up">
                    <?= $post['content'] ?>
                </div>

                <!-- Share Buttons -->
                <div class="share-section mt-5 fade-in-up">
                    <h6 class="share-title"><i class="fas fa-share-alt me-2"></i>Bagikan Artikel Ini</h6>
                    <div class="share-buttons">
                        <?php if (!empty($waNumber)): ?>
                        <a href="https://api.whatsapp.com/send?phone=<?= htmlspecialchars(preg_replace('/\D/', '', $waNumber)) ?>&text=<?= $shareTitle ?>%20<?= $shareUrl ?>"
                           class="share-btn share-btn-whatsapp" target="_blank" rel="noopener" title="Bagikan via WhatsApp">
                            <i class="fab fa-whatsapp me-2"></i>WhatsApp
                        </a>
                        <?php else: ?>
                        <a href="https://api.whatsapp.com/send?text=<?= $shareTitle ?>%20<?= $shareUrl ?>"
                           class="share-btn share-btn-whatsapp" target="_blank" rel="noopener" title="Bagikan via WhatsApp">
                            <i class="fab fa-whatsapp me-2"></i>WhatsApp
                        </a>
                        <?php endif; ?>
                        <a href="https://twitter.com/intent/tweet?text=<?= $shareTitle ?>&url=<?= $shareUrl ?>"
                           class="share-btn share-btn-twitter" target="_blank" rel="noopener" title="Bagikan via Twitter/X">
                            <i class="fab fa-x-twitter me-2"></i>Twitter
                        </a>
                        <a href="https://www.facebook.com/sharer/sharer.php?u=<?= $shareUrl ?>"
                           class="share-btn share-btn-facebook" target="_blank" rel="noopener" title="Bagikan via Facebook">
                            <i class="fab fa-facebook-f me-2"></i>Facebook
                        </a>
                        <a href="https://www.linkedin.com/sharing/share-offsite/?url=<?= $shareUrl ?>"
                           class="share-btn share-btn-linkedin" target="_blank" rel="noopener" title="Bagikan via LinkedIn">
                            <i class="fab fa-linkedin-in me-2"></i>LinkedIn
                        </a>
                    </div>
                </div>

                <!-- Back to Blog -->
                <div class="mt-5 fade-in-up">
                    <a href="<?= $siteUrl ?>/blog" class="btn btn-outline-primary">
                        <i class="fas fa-arrow-left me-2"></i>Kembali ke Blog
                    </a>
                </div>
            </div>
        </div>

        <!-- Related Posts -->
        <?php if (!empty($related)): ?>
        <div class="mt-6">
            <h3 class="h4 fw-700 mb-4">Artikel Terkait</h3>
            <div class="row g-4">
                <?php foreach ($related as $rel): ?>
                <div class="col-md-4">
                    <article class="blog-card">
                        <div class="blog-card-img">
                            <?php if (!empty($rel['image'])): ?>
                                <img src="<?= $siteUrl ?>/assets/uploads/blog/<?= htmlspecialchars($rel['image']) ?>"
                                     alt="<?= htmlspecialchars($rel['title']) ?>" loading="lazy">
                            <?php else: ?>
                                <div class="blog-card-placeholder"><i class="fas fa-pen-nib"></i></div>
                            <?php endif; ?>
                        </div>
                        <div class="blog-card-body">
                            <div class="blog-card-meta">
                                <span><i class="fas fa-calendar-alt me-1"></i><?= formatDate($rel['created_at']) ?></span>
                            </div>
                            <h4 class="blog-card-title h6">
                                <a href="<?= $siteUrl ?>/blog/<?= htmlspecialchars($rel['slug']) ?>">
                                    <?= htmlspecialchars($rel['title']) ?>
                                </a>
                            </h4>
                            <a href="<?= $siteUrl ?>/blog/<?= htmlspecialchars($rel['slug']) ?>" class="blog-read-more">
                                Baca <i class="fas fa-arrow-right ms-1"></i>
                            </a>
                        </div>
                    </article>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</section>
