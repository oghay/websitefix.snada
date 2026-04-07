<?php
/**
 * Admin Blog Form (Add / Edit)
 */

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$is_edit = $id > 0;
$errors = [];
$post = [
    'title'    => '',
    'slug'     => '',
    'excerpt'  => '',
    'content'  => '',
    'image'    => '',
    'author'   => $_SESSION['admin_name'] ?? 'Admin',
    'status'   => 'draft',
    'category' => '',
];

// Load existing post for edit
if ($is_edit) {
    try {
        $existing = fetch("SELECT * FROM blog_posts WHERE id = ?", [$id]);
        if (!$existing) {
            flash('error', 'Artikel tidak ditemukan.');
            redirect('index.php?page=blog');
        }
        $post = array_merge($post, $existing);
    } catch (Exception $e) {
        flash('error', 'Gagal memuat data artikel.');
        redirect('index.php?page=blog');
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        flash('error', 'Token keamanan tidak valid.');
        redirect('index.php?page=blog' . ($is_edit ? '-form&id=' . $id : ''));
    }

    $post['title']    = sanitize($_POST['title'] ?? '');
    $post['excerpt']  = sanitize($_POST['excerpt'] ?? '');
    $post['content']  = $_POST['content'] ?? ''; // Allow HTML
    $post['author']   = sanitize($_POST['author'] ?? 'Admin');
    $post['status']   = sanitize($_POST['status'] ?? 'draft');
    $post['category'] = sanitize($_POST['category'] ?? '');
    $post['slug']    = slugify($post['title']);

    // Validate
    if (empty($post['title'])) {
        $errors[] = 'Judul artikel wajib diisi.';
    }
    if (empty($post['content'])) {
        $errors[] = 'Konten artikel wajib diisi.';
    }
    if (!in_array($post['status'], ['draft', 'published'])) {
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
            $upload_result = uploadImage($_FILES['image'], 'blog');
            if ($upload_result) {
                if ($is_edit && !empty($post['image'])) {
                    deleteImage('blog/' . $post['image']);
                }
                $post['image'] = $upload_result;
                $image_changed = true;
            } else {
                $errors[] = 'Gagal mengunggah gambar.';
            }
        }
    }

    if (empty($errors)) {
        try {
            // Ensure unique slug
            if ($is_edit) {
                $slug_check = fetch("SELECT id FROM blog_posts WHERE slug = ? AND id != ?", [$post['slug'], $id]);
            } else {
                $slug_check = fetch("SELECT id FROM blog_posts WHERE slug = ?", [$post['slug']]);
            }
            if ($slug_check) {
                $post['slug'] = $post['slug'] . '-' . time();
            }

            $data = [
                'title'    => $post['title'],
                'slug'     => $post['slug'],
                'excerpt'  => $post['excerpt'],
                'content'  => $post['content'],
                'author'   => $post['author'],
                'status'   => $post['status'],
                'category' => $post['category'],
            ];

            if ($image_changed) {
                $data['image'] = $post['image'];
            } elseif (!$is_edit && !empty($post['image'])) {
                $data['image'] = $post['image'];
            }

            if ($is_edit) {
                update('blog_posts', $data, 'id = ?', [$id]);
                flash('success', 'Artikel "' . $post['title'] . '" berhasil diperbarui.');
            } else {
                insert('blog_posts', $data);
                flash('success', 'Artikel "' . $post['title'] . '" berhasil disimpan.');
            }

            redirect('index.php?page=blog');
        } catch (Exception $e) {
            $errors[] = 'Gagal menyimpan artikel: ' . $e->getMessage();
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
            <h2><?= $is_edit ? 'Edit Artikel' : 'Tulis Artikel Baru' ?></h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="index.php?page=blog">Blog</a></li>
                    <li class="breadcrumb-item active"><?= $is_edit ? 'Edit' : 'Tambah' ?></li>
                </ol>
            </nav>
        </div>
        <div class="page-header-actions">
            <a href="index.php?page=blog" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i>Kembali
            </a>
        </div>
    </div>

    <!-- Errors -->
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

    <form method="POST" enctype="multipart/form-data" data-loading id="blogForm">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">

        <div class="row g-4">
            <!-- Left: Content -->
            <div class="col-xl-8">
                <div class="admin-card mb-4">
                    <div class="admin-card-header">
                        <h5 class="admin-card-title">
                            <i class="fas fa-pen"></i>
                            Konten Artikel
                        </h5>
                    </div>
                    <div class="admin-card-body">
                        <!-- Title -->
                        <div class="mb-4">
                            <label class="form-label" for="title">
                                Judul Artikel <span class="required">*</span>
                            </label>
                            <input type="text" id="title" name="title"
                                   class="form-control"
                                   style="font-size:18px;font-weight:700;padding:12px 16px;"
                                   placeholder="Masukkan judul artikel yang menarik..."
                                   value="<?= htmlspecialchars($post['title']) ?>"
                                   oninput="autoSlug(this, document.getElementById('slugField'))"
                                   required>
                        </div>

                        <!-- Slug -->
                        <div class="mb-4">
                            <label class="form-label" for="slugField">Slug URL</label>
                            <div class="input-group">
                                <span class="input-group-text" style="background:#f8fafc;font-size:12px;color:#94a3b8;">/blog/</span>
                                <input type="text" id="slugField" name="slug_preview"
                                       class="form-control"
                                       value="<?= htmlspecialchars($post['slug']) ?>"
                                       readonly
                                       style="background:#f8fafc;color:#64748b;">
                            </div>
                        </div>

                        <!-- Excerpt -->
                        <div class="mb-4">
                            <label class="form-label" for="excerpt">
                                Ringkasan / Excerpt
                                <small class="text-muted fw-normal">(tampil di listing blog)</small>
                            </label>
                            <textarea id="excerpt" name="excerpt"
                                      class="form-control"
                                      rows="3"
                                      placeholder="Tulis ringkasan singkat artikel ini (maks. 200 karakter)..."><?= htmlspecialchars($post['excerpt']) ?></textarea>
                            <div class="form-hint">
                                <span id="excerptCount">0</span>/200 karakter
                            </div>
                        </div>

                        <!-- Content Editor -->
                        <div class="mb-4">
                            <label class="form-label">
                                Konten Artikel <span class="required">*</span>
                            </label>

                            <!-- Simple HTML Toolbar -->
                            <div class="editor-toolbar" id="editorToolbar">
                                <button type="button" class="toolbar-btn" onclick="insertTag('h2')" title="Heading 2"><b>H2</b></button>
                                <button type="button" class="toolbar-btn" onclick="insertTag('h3')" title="Heading 3"><b>H3</b></button>
                                <div class="toolbar-sep"></div>
                                <button type="button" class="toolbar-btn" onclick="insertWrap('<strong>', '</strong>')" title="Bold"><b>B</b></button>
                                <button type="button" class="toolbar-btn" onclick="insertWrap('<em>', '</em>')" title="Italic"><i>I</i></button>
                                <div class="toolbar-sep"></div>
                                <button type="button" class="toolbar-btn" onclick="insertTag('p')" title="Paragraf">¶</button>
                                <button type="button" class="toolbar-btn" onclick="insertUl()" title="Bullet List"><i class="fas fa-list-ul fa-xs"></i></button>
                                <button type="button" class="toolbar-btn" onclick="insertOl()" title="Numbered List"><i class="fas fa-list-ol fa-xs"></i></button>
                                <div class="toolbar-sep"></div>
                                <button type="button" class="toolbar-btn" onclick="insertLink()" title="Link"><i class="fas fa-link fa-xs"></i></button>
                                <button type="button" class="toolbar-btn" onclick="insertWrap('<blockquote>', '</blockquote>')" title="Blockquote"><i class="fas fa-quote-left fa-xs"></i></button>
                                <button type="button" class="toolbar-btn" onclick="insertWrap('<code>', '</code>')" title="Kode"><i class="fas fa-code fa-xs"></i></button>
                                <div class="toolbar-sep"></div>
                                <button type="button" class="toolbar-btn" onclick="insertTag('hr', true)" title="Garis Pemisah">—</button>
                            </div>

                            <textarea id="content" name="content"
                                      class="form-control editor-textarea"
                                      placeholder="Tulis konten artikel di sini menggunakan HTML...

Contoh:
<p>Paragraf pertama artikel Anda.</p>
<h2>Subjudul</h2>
<p>Isi konten lebih lanjut...</p>"
                                      required><?= htmlspecialchars($post['content']) ?></textarea>

                            <div class="form-hint mt-2">
                                <i class="fas fa-info-circle me-1"></i>
                                Editor mendukung HTML. Gunakan toolbar di atas untuk menyisipkan format.
                                <a href="#" onclick="togglePreview();return false;" style="color:var(--secondary);font-weight:600;">
                                    <i class="fas fa-eye ms-2 me-1"></i>Preview HTML
                                </a>
                            </div>
                        </div>

                        <!-- HTML Preview -->
                        <div id="htmlPreview" style="display:none;padding:20px;background:#fff;border:1.5px solid #e2e8f0;border-radius:10px;min-height:120px;">
                            <div style="font-size:11px;font-weight:700;text-transform:uppercase;color:#94a3b8;margin-bottom:12px;letter-spacing:0.08em;">
                                <i class="fas fa-eye me-1"></i>Preview Konten
                                <button type="button" onclick="togglePreview()" style="float:right;background:none;border:none;font-size:11px;color:#64748b;cursor:pointer;text-transform:none;font-weight:400;">
                                    Sembunyikan
                                </button>
                            </div>
                            <div id="previewContent" style="font-family:'Plus Jakarta Sans',sans-serif;line-height:1.7;"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right: Settings -->
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
                        <!-- Category -->
                        <div class="mb-3">
                            <label class="form-label" for="category">Kategori</label>
                            <select id="category" name="category" class="form-select">
                                <option value="" <?= $post['category'] === '' ? 'selected' : '' ?>>-- Pilih Kategori --</option>
                                <option value="tips" <?= $post['category'] === 'tips' ? 'selected' : '' ?>>Tips & Trik</option>
                                <option value="tutorial" <?= $post['category'] === 'tutorial' ? 'selected' : '' ?>>Tutorial</option>
                                <option value="berita" <?= $post['category'] === 'berita' ? 'selected' : '' ?>>Berita & Update</option>
                                <option value="panduan" <?= $post['category'] === 'panduan' ? 'selected' : '' ?>>Panduan OJS</option>
                                <option value="indeksasi" <?= $post['category'] === 'indeksasi' ? 'selected' : '' ?>>Indeksasi Jurnal</option>
                                <option value="lainnya" <?= $post['category'] === 'lainnya' ? 'selected' : '' ?>>Lainnya</option>
                            </select>
                        </div>

                        <!-- Status -->
                        <div class="mb-3">
                            <label class="form-label" for="status">Status</label>
                            <select id="status" name="status" class="form-select">
                                <option value="draft" <?= $post['status'] === 'draft' ? 'selected' : '' ?>>
                                    📝 Draft (Tidak Dipublikasikan)
                                </option>
                                <option value="published" <?= $post['status'] === 'published' ? 'selected' : '' ?>>
                                    ✅ Publikasi (Tampil di Website)
                                </option>
                            </select>
                        </div>

                        <!-- Author -->
                        <div class="mb-4">
                            <label class="form-label" for="author">Penulis</label>
                            <input type="text" id="author" name="author"
                                   class="form-control"
                                   placeholder="Nama penulis"
                                   value="<?= htmlspecialchars($post['author']) ?>">
                        </div>

                        <!-- Submit -->
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>
                                <?= $is_edit ? 'Perbarui Artikel' : 'Simpan Artikel' ?>
                            </button>
                            <a href="index.php?page=blog" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-2"></i>Batal
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Featured Image -->
                <div class="admin-card">
                    <div class="admin-card-header">
                        <h5 class="admin-card-title">
                            <i class="fas fa-image"></i>
                            Gambar Utama
                        </h5>
                    </div>
                    <div class="admin-card-body">
                        <!-- Current Image -->
                        <?php if ($is_edit && !empty($post['image'])): ?>
                        <div class="mb-3">
                            <div style="font-size:12px;font-weight:600;color:#64748b;margin-bottom:8px;">Gambar Saat Ini:</div>
                            <img src="../assets/uploads/blog/<?= htmlspecialchars($post['image']) ?>"
                                 alt="" id="imgPreview"
                                 style="width:100%;height:160px;object-fit:cover;border-radius:8px;border:1.5px solid #e2e8f0;">
                        </div>
                        <?php else: ?>
                        <div id="previewContainer" style="display:none;margin-bottom:12px;">
                            <img id="imgPreview" src="" alt="Preview"
                                 style="width:100%;height:160px;object-fit:cover;border-radius:8px;border:1.5px solid #e2e8f0;display:none;">
                        </div>
                        <?php endif; ?>

                        <div class="mb-2">
                            <input type="file" id="image" name="image"
                                   class="form-control"
                                   accept="image/*"
                                   onchange="previewImage(this, 'imgPreview'); showBlogPreview();">
                        </div>
                        <div class="form-hint">
                            JPG, PNG, WEBP. Maks 2MB.
                            Rekomendasi: 1200×630px (16:9).
                        </div>
                    </div>
                </div>

                <!-- Writing Tips -->
                <div class="admin-card mt-4" style="background:linear-gradient(135deg,rgba(13,148,136,0.04),rgba(26,54,93,0.04));">
                    <div class="admin-card-body">
                        <div style="font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:0.08em;color:#64748b;margin-bottom:12px;">
                            <i class="fas fa-lightbulb me-1" style="color:#d97706;"></i>
                            Tips Penulisan
                        </div>
                        <ul style="font-size:12px;color:#64748b;padding-left:16px;margin:0;line-height:1.8;">
                            <li>Gunakan judul yang menarik dan mengandung kata kunci</li>
                            <li>Tulis excerpt yang ringkas (maks. 200 karakter)</li>
                            <li>Bagi konten dengan heading H2 dan H3</li>
                            <li>Tambahkan gambar untuk meningkatkan keterbacaan</li>
                            <li>Panjang ideal: 800–2000 kata</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<?php
$extra_js = '
<script>
// ---- Excerpt character count ----
const excerptEl = document.getElementById("excerpt");
const excerptCount = document.getElementById("excerptCount");

function updateExcerptCount() {
    if (excerptEl && excerptCount) {
        excerptCount.textContent = excerptEl.value.length;
        excerptCount.style.color = excerptEl.value.length > 200 ? "#dc2626" : "#64748b";
    }
}

if (excerptEl) {
    excerptEl.addEventListener("input", updateExcerptCount);
    updateExcerptCount();
}

// ---- HTML Toolbar Functions ----
function getContentArea() {
    return document.getElementById("content");
}

function insertTag(tag, selfClose) {
    const ta = getContentArea();
    const start = ta.selectionStart;
    const end   = ta.selectionEnd;
    const selected = ta.value.substring(start, end);

    let insertion;
    if (selfClose) {
        insertion = "<" + tag + ">\n";
    } else if (selected) {
        insertion = "<" + tag + ">" + selected + "</" + tag + ">";
    } else {
        insertion = "<" + tag + ">Teks di sini</" + tag + ">";
    }

    ta.value = ta.value.substring(0, start) + insertion + ta.value.substring(end);
    ta.selectionStart = start + insertion.length;
    ta.selectionEnd   = start + insertion.length;
    ta.focus();
}

function insertWrap(open, close) {
    const ta = getContentArea();
    const start = ta.selectionStart;
    const end   = ta.selectionEnd;
    const selected = ta.value.substring(start, end);
    const insertion = open + (selected || "teks") + close;
    ta.value = ta.value.substring(0, start) + insertion + ta.value.substring(end);
    ta.selectionStart = start + insertion.length;
    ta.selectionEnd   = start + insertion.length;
    ta.focus();
}

function insertUl() {
    const ta = getContentArea();
    const pos = ta.selectionStart;
    const insertion = "\n<ul>\n    <li>Item 1</li>\n    <li>Item 2</li>\n    <li>Item 3</li>\n</ul>\n";
    ta.value = ta.value.substring(0, pos) + insertion + ta.value.substring(pos);
    ta.selectionStart = ta.selectionEnd = pos + insertion.length;
    ta.focus();
}

function insertOl() {
    const ta = getContentArea();
    const pos = ta.selectionStart;
    const insertion = "\n<ol>\n    <li>Item 1</li>\n    <li>Item 2</li>\n    <li>Item 3</li>\n</ol>\n";
    ta.value = ta.value.substring(0, pos) + insertion + ta.value.substring(pos);
    ta.selectionStart = ta.selectionEnd = pos + insertion.length;
    ta.focus();
}

function insertLink() {
    const url  = prompt("Masukkan URL link:", "https://");
    const text = prompt("Teks link:", "Baca selengkapnya");
    if (url && text) {
        insertToContent("<a href=\"" + url + "\">" + text + "</a>");
    }
}

function insertToContent(text) {
    const ta = getContentArea();
    const pos = ta.selectionStart;
    ta.value = ta.value.substring(0, pos) + text + ta.value.substring(pos);
    ta.selectionStart = ta.selectionEnd = pos + text.length;
    ta.focus();
}

// ---- HTML Preview ----
function togglePreview() {
    const preview = document.getElementById("htmlPreview");
    const content = document.getElementById("content");
    const previewContent = document.getElementById("previewContent");

    if (preview.style.display === "none") {
        previewContent.innerHTML = content.value;
        preview.style.display = "block";
        preview.scrollIntoView({ behavior: "smooth", block: "nearest" });
    } else {
        preview.style.display = "none";
    }
}

// ---- Show blog image preview ----
function showBlogPreview() {
    const container = document.getElementById("previewContainer");
    const img = document.getElementById("imgPreview");
    if (container) container.style.display = "block";
    if (img) img.style.display = "block";
}
</script>
';
require_once ADMIN_PATH . '/includes/footer.php';
?>
