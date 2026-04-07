<?php
/**
 * Admin Portofolio List
 */

// Handle delete action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        flash('error', 'Token keamanan tidak valid.');
        redirect('index.php?page=portofolio');
    }

    $id = (int)($_POST['id'] ?? 0);
    if ($id > 0) {
        try {
            // Get image to delete
            $item = fetch("SELECT image FROM portfolio WHERE id = ?", [$id]);
            if ($item && $item['image']) {
                deleteImage('portfolio/' . $item['image']);
            }
            delete('portfolio', 'id = ?', [$id]);
            flash('success', 'Portofolio berhasil dihapus.');
        } catch (Exception $e) {
            flash('error', 'Gagal menghapus portofolio: ' . $e->getMessage());
        }
    }
    redirect('index.php?page=portofolio');
}

// Handle status toggle
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'toggle_status') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        flash('error', 'Token keamanan tidak valid.');
        redirect('index.php?page=portofolio');
    }

    $id = (int)($_POST['id'] ?? 0);
    $current_status = sanitize($_POST['current_status'] ?? '');
    $new_status = $current_status === 'published' ? 'draft' : 'published';

    if ($id > 0) {
        try {
            update('portfolio', ['status' => $new_status], 'id = ?', [$id]);
            flash('success', 'Status portofolio berhasil diubah menjadi ' . ($new_status === 'published' ? 'Publikasi' : 'Draft') . '.');
        } catch (Exception $e) {
            flash('error', 'Gagal mengubah status.');
        }
    }
    redirect('index.php?page=portofolio');
}

// Fetch all portfolio items
$portfolio_items = [];
try {
    $portfolio_items = fetchAll(
        "SELECT id, title, slug, image, client_name, client_institution, category, is_featured, status, created_at
         FROM portfolio ORDER BY created_at DESC"
    );
} catch (Exception $e) {}

$csrf = csrf_token();

$category_labels = [
    'jurnal'      => 'Jurnal',
    'konferensi'  => 'Konferensi',
    'repositori'  => 'Repositori',
    'lainnya'     => 'Lainnya',
];

require_once ADMIN_PATH . '/includes/header.php';
require_once ADMIN_PATH . '/includes/sidebar.php';
?>

<div class="admin-content">
    <!-- Page Header -->
    <div class="page-header">
        <div class="page-header-left">
            <h2>Kelola Portofolio</h2>
            <p>Daftar semua proyek portofolio yang ditampilkan di website.</p>
        </div>
        <div class="page-header-actions">
            <a href="index.php?page=portofolio-form" class="btn btn-primary">
                <i class="fas fa-plus"></i>
                Tambah Portofolio
            </a>
        </div>
    </div>

    <!-- Portfolio Table Card -->
    <div class="admin-card">
        <div class="admin-card-header">
            <h5 class="admin-card-title">
                <i class="fas fa-briefcase"></i>
                Daftar Portofolio
                <span class="badge" style="background:#e2e8f0;color:#475569;font-size:11px;margin-left:4px;">
                    <?= count($portfolio_items) ?> item
                </span>
            </h5>
            <div style="display:flex;gap:8px;align-items:center;">
                <a href="index.php?page=portofolio-form" class="btn btn-sm btn-secondary">
                    <i class="fas fa-plus me-1"></i>Tambah Baru
                </a>
            </div>
        </div>
        <div class="admin-card-body">
            <?php if (!empty($portfolio_items)): ?>
            <div class="table-responsive">
                <table class="table admin-datatable" id="portfolioTable">
                    <thead>
                        <tr>
                            <th width="40">#</th>
                            <th width="60">Gambar</th>
                            <th>Judul</th>
                            <th>Klien / Institusi</th>
                            <th>Kategori</th>
                            <th>Featured</th>
                            <th>Status</th>
                            <th>Tanggal</th>
                            <th width="130">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($portfolio_items as $i => $item): ?>
                        <tr>
                            <td><?= $i + 1 ?></td>
                            <td>
                                <?php if ($item['image']): ?>
                                    <img src="../assets/uploads/portfolio/<?= htmlspecialchars($item['image']) ?>"
                                         alt="<?= htmlspecialchars($item['title']) ?>"
                                         class="table-thumbnail"
                                         onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNTAiIGhlaWdodD0iMzgiIHZpZXdCb3g9IjAgMCA1MCAzOCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iNTAiIGhlaWdodD0iMzgiIGZpbGw9IiNmMWY1ZjkiLz48dGV4dCB4PSIyNSIgeT0iMjAiIHRleHQtYW5jaG9yPSJtaWRkbGUiIGZpbGw9IiM5NGEzYjgiIGZvbnQtc2l6ZT0iMTAiPk5vIEltZzwvdGV4dD48L3N2Zz4='">
                                <?php else: ?>
                                    <div style="width:50px;height:38px;background:#f1f5f9;border-radius:6px;display:flex;align-items:center;justify-content:center;">
                                        <i class="fas fa-image" style="color:#94a3b8;font-size:16px;"></i>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div style="font-weight:600;max-width:200px;">
                                    <?= htmlspecialchars(truncate($item['title'], 50)) ?>
                                </div>
                                <div style="font-size:11px;color:#94a3b8;"><?= htmlspecialchars($item['slug']) ?></div>
                            </td>
                            <td>
                                <div style="font-size:13px;"><?= htmlspecialchars($item['client_name'] ?? '-') ?></div>
                                <div style="font-size:11px;color:#94a3b8;"><?= htmlspecialchars($item['client_institution'] ?? '') ?></div>
                            </td>
                            <td>
                                <span class="badge" style="background:#e2e8f0;color:#475569;">
                                    <?= htmlspecialchars($category_labels[$item['category']] ?? $item['category']) ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($item['is_featured']): ?>
                                    <span class="badge" style="background:#fef3c7;color:#92400e;">
                                        <i class="fas fa-star me-1"></i>Featured
                                    </span>
                                <?php else: ?>
                                    <span style="color:#94a3b8;font-size:12px;">—</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge badge-status-<?= htmlspecialchars($item['status']) ?>">
                                    <?= $item['status'] === 'published' ? 'Publikasi' : 'Draft' ?>
                                </span>
                            </td>
                            <td style="font-size:12px;color:#64748b;white-space:nowrap;">
                                <?= function_exists('formatDate') ? formatDate($item['created_at']) : date('d M Y', strtotime($item['created_at'])) ?>
                            </td>
                            <td>
                                <div class="action-btns">
                                    <!-- Edit -->
                                    <a href="index.php?page=portofolio-form&id=<?= $item['id'] ?>"
                                       class="btn btn-xs btn-outline-primary"
                                       data-bs-toggle="tooltip" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>

                                    <!-- Toggle Status -->
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
                                        <input type="hidden" name="action" value="toggle_status">
                                        <input type="hidden" name="id" value="<?= $item['id'] ?>">
                                        <input type="hidden" name="current_status" value="<?= htmlspecialchars($item['status']) ?>">
                                        <button type="submit"
                                                class="btn btn-xs btn-outline-secondary"
                                                data-bs-toggle="tooltip"
                                                title="<?= $item['status'] === 'published' ? 'Jadikan Draft' : 'Publikasikan' ?>">
                                            <i class="fas <?= $item['status'] === 'published' ? 'fa-eye-slash' : 'fa-eye' ?>"></i>
                                        </button>
                                    </form>

                                    <!-- Delete -->
                                    <form method="POST" style="display:inline;"
                                          onsubmit="return confirmDelete(this, '<?= addslashes(htmlspecialchars($item['title'])) ?>')">
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?= $item['id'] ?>">
                                        <button type="submit"
                                                class="btn btn-xs btn-outline-danger"
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
                <i class="fas fa-briefcase empty-state-icon"></i>
                <h4>Belum Ada Portofolio</h4>
                <p>Tambahkan proyek portofolio pertama Anda untuk ditampilkan di website.</p>
                <a href="index.php?page=portofolio-form" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>Tambah Portofolio
                </a>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once ADMIN_PATH . '/includes/footer.php'; ?>
