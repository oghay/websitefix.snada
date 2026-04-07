    <!-- Admin Footer -->
    <footer class="admin-footer">
        <div>
            &copy; <?= date('Y') ?> <?= function_exists('getSetting') ? htmlspecialchars(getSetting('site_name', 'OJS Developer Indonesia')) : 'OJS Developer Indonesia' ?> &mdash; Admin Panel
        </div>
        <div>
            Dibuat dengan <i class="fas fa-heart text-danger" style="font-size:12px;"></i> untuk kemajuan jurnal akademik Indonesia
        </div>
    </footer>
</div><!-- /.admin-main -->
</div><!-- /.admin-wrapper -->

<!-- Bootstrap 5.3.3 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- jQuery (required by DataTables) -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

<script>
/* ============================================================
   COMMON ADMIN JAVASCRIPT
============================================================ */

// ---- Sidebar Toggle ----
function toggleSidebar() {
    const sidebar = document.getElementById('adminSidebar');
    const overlay = document.getElementById('sidebarOverlay');

    if (window.innerWidth <= 768) {
        // Mobile: slide in/out drawer
        sidebar.classList.toggle('mobile-open');
        overlay.classList.toggle('active');
    } else {
        // Desktop/Tablet: toggle collapsed
        sidebar.classList.toggle('collapsed');
        const main = document.getElementById('adminMain');
        if (sidebar.classList.contains('collapsed')) {
            main.style.marginLeft = 'var(--sidebar-collapsed-width)';
        } else {
            main.style.marginLeft = 'var(--sidebar-width)';
        }
    }
}

function closeSidebar() {
    const sidebar = document.getElementById('adminSidebar');
    const overlay = document.getElementById('sidebarOverlay');
    sidebar.classList.remove('mobile-open');
    overlay.classList.remove('active');
}

// Handle resize
window.addEventListener('resize', function() {
    const sidebar = document.getElementById('adminSidebar');
    const overlay = document.getElementById('sidebarOverlay');
    if (window.innerWidth > 768) {
        sidebar.classList.remove('mobile-open');
        overlay.classList.remove('active');
    }
});

// ---- Flash Message Auto-Dismiss ----
function dismissFlash(btn) {
    const msg = btn.closest('.flash-message');
    msg.classList.add('flash-out');
    setTimeout(() => msg.remove(), 320);
}

// Auto-dismiss after 5 seconds
document.addEventListener('DOMContentLoaded', function() {
    const flashes = document.querySelectorAll('.flash-message');
    flashes.forEach(function(msg, i) {
        setTimeout(function() {
            if (msg.parentNode) {
                msg.classList.add('flash-out');
                setTimeout(() => { if (msg.parentNode) msg.remove(); }, 320);
            }
        }, 5000 + (i * 500));
    });
});

// ---- Confirm Delete ----
function confirmDelete(form, itemName) {
    itemName = itemName || 'item ini';
    if (confirm('Apakah Anda yakin ingin menghapus ' + itemName + '?\n\nTindakan ini tidak dapat dibatalkan.')) {
        form.submit();
        return true;
    }
    return false;
}

// Shorthand for delete confirmation
function deleteConfirm(name) {
    return confirm('Hapus "' + (name || 'item ini') + '"?\n\nTindakan ini tidak dapat dibatalkan.');
}

// ---- Image Preview on Upload ----
function previewImage(input, previewId) {
    if (input.files && input.files[0]) {
        const file = input.files[0];

        // Validate type
        const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!allowedTypes.includes(file.type)) {
            alert('Tipe file tidak didukung. Harap unggah gambar (JPG, PNG, GIF, WEBP).');
            input.value = '';
            return;
        }

        // Validate size (max 2MB)
        if (file.size > 2 * 1024 * 1024) {
            alert('Ukuran file terlalu besar. Maksimal 2MB.');
            input.value = '';
            return;
        }

        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById(previewId);
            if (preview) {
                preview.src = e.target.result;
                preview.style.display = 'block';
                const wrap = preview.closest('.img-preview-wrap');
                if (wrap) wrap.style.display = 'inline-block';
            }
        };
        reader.readAsDataURL(file);
    }
}

// ---- Slug Auto-Generate from Title ----
function autoSlug(titleInput, slugInput) {
    const title = titleInput.value;
    // Basic slugify in JS
    let slug = title
        .toLowerCase()
        .replace(/[àáâãäå]/g, 'a')
        .replace(/[èéêë]/g, 'e')
        .replace(/[ìíîï]/g, 'i')
        .replace(/[òóôõö]/g, 'o')
        .replace(/[ùúûü]/g, 'u')
        .replace(/[ñ]/g, 'n')
        .replace(/[^a-z0-9\s-]/g, '')
        .trim()
        .replace(/\s+/g, '-')
        .replace(/-+/g, '-');
    slugInput.value = slug;
}

// ---- Loading State for Forms ----
function showLoading() {
    const overlay = document.createElement('div');
    overlay.className = 'loading-overlay';
    overlay.innerHTML = '<div class="loading-spinner"></div>';
    document.body.appendChild(overlay);
}

// ---- Form Submit with Loading ----
document.addEventListener('DOMContentLoaded', function() {
    const forms = document.querySelectorAll('form[data-loading]');
    forms.forEach(function(form) {
        form.addEventListener('submit', function() {
            showLoading();
        });
    });
});

// ---- DataTables Default Initialization ----
document.addEventListener('DOMContentLoaded', function() {
    if (typeof $.fn.DataTable !== 'undefined') {
        $('.admin-datatable').each(function() {
            $(this).DataTable({
                responsive: true,
                pageLength: 15,
                language: {
                    emptyTable:     'Tidak ada data yang tersedia',
                    info:           'Menampilkan _START_ - _END_ dari _TOTAL_ data',
                    infoEmpty:      'Menampilkan 0 - 0 dari 0 data',
                    infoFiltered:   '(difilter dari _MAX_ total data)',
                    lengthMenu:     'Tampilkan _MENU_ data',
                    loadingRecords: 'Memuat...',
                    processing:     'Memproses...',
                    search:         'Cari:',
                    zeroRecords:    'Tidak ditemukan data yang sesuai',
                    paginate: {
                        first:    '«',
                        last:     '»',
                        next:     '›',
                        previous: '‹'
                    }
                },
                dom: '<"row align-items-center mb-3"<"col-sm-6"l><"col-sm-6"f>>rt<"row align-items-center mt-3"<"col-sm-6"i><"col-sm-6"p>>',
                columnDefs: [
                    { orderable: false, targets: -1 } // Last column (actions) not sortable
                ]
            });
        });
    }
});

// ---- Tooltip initialization ----
document.addEventListener('DOMContentLoaded', function() {
    const tooltips = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    tooltips.forEach(el => new bootstrap.Tooltip(el));
});
</script>

<?php if (isset($extra_js)): ?>
    <?= $extra_js ?>
<?php endif; ?>

</body>
</html>
