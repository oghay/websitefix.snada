<?php
/**
 * Admin Blog Posts List
 */

// Handle delete action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        flash('error', 'Token keamanan tidak valid.');
        redirect('index.php?page=blog');
    }

    $id = (int)($_POST['id'] ?? 0);
    if ($id > 0) {
        try {
            $item = fetch("SELECT image FROM blog_posts WHERE id = ?", [$id]);
            if ($item && $item['image']) {
                deleteImage('blog/' . $item['image']);
            }
            delete('blog_posts', 'id = ?', [$id]);
            flash('success', 'Artikel blog berhasil dihapus.');
        } catch (Exception $e) {
            flash('error', 'Gagal menghapus artikel: ' . $e->getMessage());
        }
    }
    redirect('index.php?page=blog');
}

// Handle status toggle
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'toggle_status') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        flash('error', 'Token keamanan tidak valid.');
        redirect('index.php?page=blog');
    }

    $id = (int)($_POST['id'] ?? 0);
    $current = sanitize($_POST['current_status'] ?? '');
    $new_status = $current === 'published' ? 'draft' : 'published';

    if ($id > 0) {
        try {
            update('blog_posts', ['status' => $new_status], 'id = ?', [$id]);
            flash('success', 'Status artikel berhasil diubah.');
        } catch (Exception $e) {
            flash('error', 'Gagal mengubah status.');
        }
    }
    redirect('index.php?page=blog');
}

// Fetch all blog posts
$blog_posts = [];
try {
    $blog_posts = fetchAll(
        "SELECT id, title, slug, image, author, status, views, created_at
         FROM blog_posts ORDER BY created_at DESC"
    );
} catch (Exception $e) {}

$csrf = csrf_token();

require_once ADMIN_PATH . '/includes/header.php';
require_once ADMIN_PATH . '/includes/sidebar.php';
?>

<div class="admin-content">
    <!-- Page Header -->
    <div class="page-header">
        <div class="page-header-left">
            <h2>Kelola Blog</h2>
            <p>Daftar semua artikel yang diterbitkan di blog website.</p>
        </div>
        <div class="page-header-actions">
            <a href="index.php?page=blog-form" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i>Tambah Artikel
            </a>
        </div>
    </div>

    <!-- Stats Row -->
    <div class="row g-3 mb-4">
        <?php
        $published_count = count(array_filter($blog_posts, fn($p) => $p['status'] === 'published'));
        $draft_count     = count(array_filter($blog_posts, fn($p) => $p['status'] === 'draft'));
        $total_views     = array_sum(array_column($blog_posts, 'views'));
        ?>
        <div class="col-sm-4">
            <div style="background:#fff;border-radius:10px;padding:16px 20px;border:1px solid #e2e8f0;display:flex;align-items:center;gap:12px;">
                <div style="width:40px;height:40px;border-radius:8px;background:rgba(22,163,74,0.1);color:#16a34a;display:flex;align-items:center;justify-content:center;font-size:18px;">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div>
                    <div style="font-size:22px;font-weight:800;color:#1e293b;"><?= $published_count ?></div>
                    <div style="font-size:12px;color:#64748b;">Dipublikasikan</div>
                </div>
            </div>
        </div>
        <div class="col-sm-4">
            <div style="background:#fff;border-radius:10px;padding:16px 20px;border:1px solid #e2e8f0;display:flex;align-items:center;gap:12px;">
                <div style="width:40px;height:40px;border-radius:8px;background:rgba(217,119,6,0.1);color:#d97706;display:flex;align-items:center;justify-content:center;font-size:18px;">
                    <i class="fas fa-file-alt"></i>
                </div>
                <div>
                    <div style="font-size:22px;font-weight:800;color:#1e293b;"><?= $draft_count ?></div>
                    <div style="font-size:12px;color:#64748b;">Draft</div>
                </div>
            </div>
        </div>
        <div class="col-sm-4">
            <div style="background:#fff;border-radius:10px;padding:16px 20px;border:1px solid #e2e8f0;display:flex;align-items:center;gap:12px;">
                <div style="width:40px;height:40px;border-radius:8px;background:rgba(8,145,178,0.1);color:#0891b2;display:flex;align-items:center;justify-content:center;font-size:18px;">
                    <i class="fas fa-eye"></i>
                </div>
                <div>
                    <div style="font-size:22px;font-weight:800;color:#1e293b;"><?= number_format($total_views) ?></div>
                    <div style="font-size:12px;color:#64748b;">Total Views</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Blog Table -->
    <div class="admin-card">
        <div class="admin-card-header">
            <h5 class="admin-card-title">
                <i class="fas fa-newspaper"></i>
                Daftar Artikel
                <span class="badge" style="background:#e2e8f0;color:#475569;font-size:11px;margin-left:4px;">
                    <?= count($blog_posts) ?> artikel
                </span>
            </h5>
        </div>
        <div class="admin-card-body">
            <?php if (!empty($blog_posts)): ?>
            <div class="table-responsive">
                <table class="table admin-datatable" id="blogTable">
                    <thead>
                        <tr>
                            <th width="40">#</th>
                            <th width="60">Gambar</th>
                            <th>Judul Artikel</th>
                            <th>Penulis</th>
                            <th>Status</th>
                            <th>Views</th>
                            <th>Tanggal</th>
                            <th width="130">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($blog_posts as $i => $post): ?>
                        <tr>
                            <td><?= $i + 1 ?></td>
                            <td>
                                <?php if ($post['image']): ?>
                                    <img src="../assets/uploads/blog/<?= htmlspecialchars($post['image']) ?>"
                                         alt=""
                                         class="table-thumbnail"
                                         onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNTAiIGhlaWdodD0iMzgiIHZpZXdCb3g9IjAgMCA1MCAzOCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iNTAiIGhlaWdodD0iMzgiIGZpbGw9IiNmMWY1ZjkiLz48dGV4dCB4PSIyNSIgeT0iMjAiIHRleHQtYW5jaG9yPSJtaWRkbGUiIGZpbGw9IiM5NGEzYjgiIGZvbnQtc2l6ZT0iMTAiPk5vIEltZzwvdGV4dD48L3N2Zz4='">
                                <?php else: ?>
                                    <div style="width:50px;height:38px;background:#f1f5f9;border-radius:6px;display:flex;align-items:center;justify-content:center;">
                                        <i class="fas fa-image" style="color:#94a3b8;font-size:16px;"></i>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div style="font-weight:600;max-width:260px;">
                                    <?= htmlspecialchars(truncate($post['title'], 60)) ?>
                                </div>
                                <div style="font-size:11px;color:#94a3b8;"><?= htmlspecialchars($post['slug']) ?></div>
                            </td>
                            <td style="font-size:13px;"><?= htmlspecialchars($post['author']) ?></td>
                            <td>
                                <span class="badge badge-status-<?= htmlspecialchars($post['status']) ?>">
                                    <?= $post['status'] === 'published' ? 'Publikasi' : 'Draft' ?>
                                </span>
                            </td>
                            <td>
                                <div style="display:flex;align-items:center;gap:4px;font-size:13px;">
                                    <i class="fas fa-eye" style="color:#94a3b8;font-size:11px;"></i>
                                    <?= number_format($post['views']) ?>
                                </div>
                            </td>
                            <td style="font-size:12px;color:#64748b;white-space:nowrap;">
                                <?= function_exists('formatDate') ? formatDate($post['created_at']) : date('d M Y', strtotime($post['created_at'])) ?>
                            </td>
                            <td>
                                <div class="action-btns">
                                    <!-- Edit -->
                                    <a href="index.php?page=blog-form&id=<?= $post['id'] ?>"
                                       class="btn btn-xs btn-outline-primary"
                                       data-bs-toggle="tooltip" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>

                                    <!-- Toggle Status -->
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
                                        <input type="hidden" name="action" value="toggle_status">
                                        <input type="hidden" name="id" value="<?= $post['id'] ?>">
                                        <input type="hidden" name="current_status" value="<?= htmlspecialchars($post['status']) ?>">
                                        <button type="submit" class="btn btn-xs btn-outline-secondary"
                                                data-bs-toggle="tooltip"
                                                title="<?= $post['status'] === 'published' ? 'Jadikan Draft' : 'Publikasikan' ?>">
                                            <i class="fas <?= $post['status'] === 'published' ? 'fa-eye-slash' : 'fa-eye' ?>"></i>
                                        </button>
                                    </form>

                                    <!-- Delete -->
                                    <form method="POST" style="display:inline;"
                                          onsubmit="return confirmDelete(this, '<?= addslashes(htmlspecialchars($post['title'])) ?>')">
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?= $post['id'] ?>">
                                        <button type="submit" class="btn btn-xs btn-outline-danger"
                                                data-bs-toggle="tooltip" title="Hapus">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-newspaper empty-state-icon"></i>
                <h4>Belum Ada Artikel Blog</h4>
                <p>Mulai tulis artikel pertama Anda untuk meningkatkan visibilitas website.</p>
                <a href="index.php?page=blog-form" class="btn btn-primary">
                    <i class="fas fa-pen me-2"></i>Tulis Artikel
                </a>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once ADMIN_PATH . '/includes/footer.php'; ?>
