<?php
/**
 * Admin Portofolio Form (Add / Edit)
 */

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$is_edit = $id > 0;
$errors = [];
$item = [
    'title'              => '',
    'description'        => '',
    'image'              => '',
    'client_name'        => '',
    'client_institution' => '',
    'website_url'        => '',
    'category'           => 'jurnal',
    'is_featured'        => 0,
    'status'             => 'published',
    'slug'               => '',
];

// Load existing item for edit
if ($is_edit) {
    try {
        $existing = fetch("SELECT * FROM portfolio WHERE id = ?", [$id]);
        if (!$existing) {
            flash('error', 'Portofolio tidak ditemukan.');
            redirect('index.php?page=portofolio');
        }
        $item = array_merge($item, $existing);
    } catch (Exception $e) {
        flash('error', 'Gagal memuat data portofolio.');
        redirect('index.php?page=portofolio');
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        flash('error', 'Token keamanan tidak valid.');
        redirect('index.php?page=portofolio' . ($is_edit ? '-form&id=' . $id : ''));
    }

    // Collect form data
    $item['title']              = sanitize($_POST['title'] ?? '');
    $item['description']        = sanitize($_POST['description'] ?? '');
    $item['client_name']        = sanitize($_POST['client_name'] ?? '');
    $item['client_institution'] = sanitize($_POST['client_institution'] ?? '');
    $item['website_url']        = sanitize($_POST['website_url'] ?? '');
    $item['category']           = sanitize($_POST['category'] ?? 'jurnal');
    $item['is_featured']        = isset($_POST['is_featured']) ? 1 : 0;
    $item['status']             = sanitize($_POST['status'] ?? 'published');
    $item['slug']               = slugify($item['title']);

    // Validate
    if (empty($item['title'])) {
        $errors[] = 'Judul portofolio wajib diisi.';
    }

    if (empty($item['description'])) {
        $errors[] = 'Deskripsi portofolio wajib diisi.';
    }

    // Valid categories
    $valid_categories = ['jurnal', 'konferensi', 'repositori', 'lainnya'];
    if (!in_array($item['category'], $valid_categories)) {
        $errors[] = 'Kategori tidak valid.';
    }

    // Valid statuses
    if (!in_array($item['status'], ['draft', 'published'])) {
        $errors[] = 'Status tidak valid.';
    }

    // Handle image upload
    $image_changed = false;
    if (!empty($_FILES['image']['name'])) {
        $allowed_ext = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $file_ext    = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));

        if (!in_array($file_ext, $allowed_ext)) {
            $errors[] = 'Format gambar tidak didukung. Gunakan JPG, PNG, GIF, atau WEBP.';
        } elseif ($_FILES['image']['size'] > 2 * 1024 * 1024) {
            $errors[] = 'Ukuran gambar terlalu besar. Maksimal 2MB.';
        } else {
            $upload_result = uploadImage($_FILES['image'], 'portfolio');
            if ($upload_result) {
                // Delete old image if editing
                if ($is_edit && !empty($item['image'])) {
                    deleteImage('portfolio/' . $item['image']);
                }
                $item['image'] = $upload_result;
                $image_changed = true;
            } else {
                $errors[] = 'Gagal mengunggah gambar. Silakan coba lagi.';
            }
        }
    }

    if (empty($errors)) {
        try {
            // Check slug uniqueness
            if ($is_edit) {
                $slug_check = fetch(
                    "SELECT id FROM portfolio WHERE slug = ? AND id != ?",
                    [$item['slug'], $id]
                );
            } else {
                $slug_check = fetch("SELECT id FROM portfolio WHERE slug = ?", [$item['slug']]);
            }

            // Append ID to slug if duplicate
            if ($slug_check) {
                $item['slug'] = $item['slug'] . '-' . time();
            }

            $data = [
                'title'              => $item['title'],
                'slug'               => $item['slug'],
                'description'        => $item['description'],
                'client_name'        => $item['client_name'],
                'client_institution' => $item['client_institution'],
                'website_url'        => $item['website_url'],
                'category'           => $item['category'],
                'is_featured'        => $item['is_featured'],
                'status'             => $item['status'],
            ];

            if ($image_changed || (!$is_edit && !empty($item['image']))) {
                $data['image'] = $item['image'];
            }

            if ($is_edit) {
                update('portfolio', $data, 'id = ?', [$id]);
                flash('success', 'Portofolio "' . $item['title'] . '" berhasil diperbarui.');
            } else {
                insert('portfolio', $data);
                flash('success', 'Portofolio "' . $item['title'] . '" berhasil ditambahkan.');
            }

            redirect('index.php?page=portofolio');
        } catch (Exception $e) {
            $errors[] = 'Gagal menyimpan data: ' . $e->getMessage();
        }
    }
}

$csrf = csrf_token();

require_once ADMIN_PATH . '/includes/header.php';
require_once ADMIN_PATH . '/includes/sidebar.php';
?>

<div class="admin-content">
    <!-- Page Header -->
    <div class="page-header">
        <div class="page-header-left">
            <h2><?= $is_edit ? 'Edit Portofolio' : 'Tambah Portofolio' ?></h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="index.php?page=portofolio">Portofolio</a></li>
                    <li class="breadcrumb-item active"><?= $is_edit ? 'Edit' : 'Tambah' ?></li>
                </ol>
            </nav>
        </div>
        <div class="page-header-actions">
            <a href="index.php?page=portofolio" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i>Kembali
            </a>
        </div>
    </div>

    <!-- Error Messages -->
    <?php if (!empty($errors)): ?>
    <div class="alert alert-danger mb-4">
        <strong><i class="fas fa-exclamation-triangle me-2"></i>Terdapat kesalahan:</strong>
        <ul class="mb-0 mt-2 ps-3">
            <?php foreach ($errors as $err): ?>
                <li><?= htmlspecialchars($err) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" data-loading id="portfolioForm">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">

        <div class="row g-4">
            <!-- Left Column: Main Content -->
            <div class="col-xl-8">
                <div class="admin-card mb-4">
                    <div class="admin-card-header">
                        <h5 class="admin-card-title">
                            <i class="fas fa-info-circle"></i>
                            Informasi Portofolio
                        </h5>
                    </div>
                    <div class="admin-card-body">
                        <!-- Title -->
                        <div class="mb-4">
                            <label class="form-label" for="title">
                                Judul Proyek <span class="required">*</span>
                            </label>
                            <input type="text" id="title" name="title"
                                   class="form-control"
                                   placeholder="Contoh: Jurnal Pendidikan Universitas Indonesia"
                                   value="<?= htmlspecialchars($item['title']) ?>"
                                   oninput="autoSlug(this, document.getElementById('slug'))"
                                   required>
                        </div>

                        <!-- Slug -->
                        <div class="mb-4">
                            <label class="form-label" for="slug">
                                Slug URL
                                <small class="text-muted fw-normal">(auto-generate dari judul)</small>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text" style="background:#f8fafc;font-size:12px;color:#94a3b8;">/portofolio/</span>
                                <input type="text" id="slug" name="slug_preview"
                                       class="form-control"
                                       value="<?= htmlspecialchars($item['slug']) ?>"
                                       readonly
                                       style="background:#f8fafc;color:#64748b;">
                            </div>
                            <div class="form-hint">Slug dihasilkan otomatis dari judul.</div>
                        </div>

                        <!-- Description -->
                        <div class="mb-4">
                            <label class="form-label" for="description">
                                Deskripsi Proyek <span class="required">*</span>
                            </label>
                            <textarea id="description" name="description"
                                      class="form-control"
                                      rows="6"
                                      placeholder="Deskripsikan proyek ini, tantangan yang dihadapi, dan solusi yang diberikan..."
                                      required><?= htmlspecialchars($item['description']) ?></textarea>
                        </div>

                        <!-- Website URL -->
                        <div class="mb-4">
                            <label class="form-label" for="website_url">
                                URL Website
                                <small class="text-muted fw-normal">(opsional)</small>
                            </label>
                            <input type="url" id="website_url" name="website_url"
                                   class="form-control"
                                   placeholder="https://jurnal.contoh.ac.id"
                                   value="<?= htmlspecialchars($item['website_url']) ?>">
                        </div>
                    </div>
                </div>

                <!-- Client Info -->
                <div class="admin-card">
                    <div class="admin-card-header">
                        <h5 class="admin-card-title">
                            <i class="fas fa-building"></i>
                            Informasi Klien
                        </h5>
                    </div>
                    <div class="admin-card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label" for="client_name">Nama Klien</label>
                                <input type="text" id="client_name" name="client_name"
                                       class="form-control"
                                       placeholder="Dr. Ahmad Fauzi, M.Pd"
                                       value="<?= htmlspecialchars($item['client_name']) ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="client_institution">Institusi / Lembaga</label>
                                <input type="text" id="client_institution" name="client_institution"
                                       class="form-control"
                                       placeholder="Universitas Negeri Jakarta"
                                       value="<?= htmlspecialchars($item['client_institution']) ?>">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Settings & Image -->
            <div class="col-xl-4">
                <!-- Publish Settings -->
                <div class="admin-card mb-4">
                    <div class="admin-card-header">
                        <h5 class="admin-card-title">
                            <i class="fas fa-cog"></i>
                            Pengaturan Publikasi
                        </h5>
                    </div>
                    <div class="admin-card-body">
                        <!-- Status -->
                        <div class="mb-3">
                            <label class="form-label" for="status">Status</label>
                            <select id="status" name="status" class="form-select">
                                <option value="published" <?= $item['status'] === 'published' ? 'selected' : '' ?>>
                                    ✅ Publikasi
                                </option>
                                <option value="draft" <?= $item['status'] === 'draft' ? 'selected' : '' ?>>
                                    📝 Draft
                                </option>
                            </select>
                        </div>

                        <!-- Category -->
                        <div class="mb-3">
                            <label class="form-label" for="category">Kategori</label>
                            <select id="category" name="category" class="form-select">
                                <option value="jurnal" <?= $item['category'] === 'jurnal' ? 'selected' : '' ?>>Jurnal</option>
                                <option value="konferensi" <?= $item['category'] === 'konferensi' ? 'selected' : '' ?>>Konferensi</option>
                                <option value="repositori" <?= $item['category'] === 'repositori' ? 'selected' : '' ?>>Repositori</option>
                                <option value="lainnya" <?= $item['category'] === 'lainnya' ? 'selected' : '' ?>>Lainnya</option>
                            </select>
                        </div>

                        <!-- Featured -->
                        <div class="mb-3">
                            <div class="form-check" style="padding:14px 16px;background:#f8fafc;border-radius:8px;border:1.5px solid #e2e8f0;">
                                <input class="form-check-input" type="checkbox"
                                       id="is_featured" name="is_featured" value="1"
                                       <?= $item['is_featured'] ? 'checked' : '' ?>
                                       style="width:18px;height:18px;margin-top:2px;">
                                <label class="form-check-label ms-2" for="is_featured">
                                    <span style="font-weight:600;font-size:13.5px;">
                                        <i class="fas fa-star me-1" style="color:#d97706;"></i>
                                        Tampilkan sebagai Featured
                                    </span>
                                    <div style="font-size:12px;color:#64748b;">Ditampilkan di bagian utama halaman portofolio</div>
                                </label>
                            </div>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>
                                <?= $is_edit ? 'Perbarui Portofolio' : 'Simpan Portofolio' ?>
                            </button>
                            <a href="index.php?page=portofolio" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-2"></i>Batal
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Image Upload -->
                <div class="admin-card">
                    <div class="admin-card-header">
                        <h5 class="admin-card-title">
                            <i class="fas fa-image"></i>
                            Gambar Portofolio
                        </h5>
                    </div>
                    <div class="admin-card-body">
                        <!-- Current Image Preview -->
                        <?php if ($is_edit && !empty($item['image'])): ?>
                        <div class="mb-3">
                            <div style="font-size:12px;font-weight:600;color:#64748b;margin-bottom:8px;">Gambar Saat Ini:</div>
                            <div class="img-preview-wrap" id="currentImgWrap">
                                <img src="../assets/uploads/portfolio/<?= htmlspecialchars($item['image']) ?>"
                                     alt="Current"
                                     class="img-preview img-preview-large"
                                     id="imgPreview"
                                     style="width:100%;height:160px;">
                            </div>
                        </div>
                        <?php else: ?>
                        <div id="previewContainer" style="display:none;margin-bottom:12px;">
                            <div style="font-size:12px;font-weight:600;color:#64748b;margin-bottom:8px;">Preview:</div>
                            <div class="img-preview-wrap">
                                <img id="imgPreview" src="" alt="Preview"
                                     class="img-preview" style="width:100%;height:160px;display:none;">
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- File Input -->
                        <div class="mb-3">
                            <label class="form-label" for="image">
                                <?= ($is_edit && !empty($item['image'])) ? 'Ganti Gambar' : 'Pilih Gambar' ?>
                            </label>
                            <input type="file" id="image" name="image"
                                   class="form-control"
                                   accept="image/*"
                                   onchange="previewImage(this, 'imgPreview'); showPreviewContainer();">
                            <div class="form-hint">
                                <i class="fas fa-info-circle me-1"></i>
                                Format: JPG, PNG, WEBP. Maks: 2MB.
                                Rekomendasi: 800×600px atau 16:9.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<?php
$extra_js = '
<script>
function showPreviewContainer() {
    const container = document.getElementById("previewContainer");
    const preview = document.getElementById("imgPreview");
    if (container) container.style.display = "block";
    if (preview) preview.style.display = "block";
}
</script>
';
require_once ADMIN_PATH . '/includes/footer.php';
?>
