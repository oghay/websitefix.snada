# OJS Developer Indonesia - Website Jasa OJS

Website profesional untuk jasa pembuatan dan pengelolaan website jurnal Open Journal Systems (OJS). Dibangun dengan PHP native + MySQL, siap deploy di shared hosting (cPanel).

## Persyaratan Sistem

- PHP 7.4 atau lebih baru
- MySQL 5.7+ / MariaDB 10.3+
- Apache dengan mod_rewrite aktif
- Ekstensi PHP: PDO, PDO_MySQL, FileInfo
- Akses phpMyAdmin (opsional)

## Cara Instalasi

### 1. Upload File
- Upload seluruh folder `ojs-developer/` ke `public_html/` di cPanel
- Atau upload ke subfolder, misal `public_html/ojs-developer/`

### 2. Buat Database
- Masuk ke phpMyAdmin di cPanel
- Buat database baru (misal: `ojs_developer`)
- Buat user database dan berikan semua privileges

### 3. Jalankan Installer
- Buka browser, akses: `https://domainanda.com/install.php`
- Atau jika di subfolder: `https://domainanda.com/ojs-developer/install.php`
- Ikuti langkah instalasi:
  - Step 1: Cek persyaratan sistem
  - Step 2: Isi kredensial database + buat akun admin
- Installer akan otomatis membuat tabel dan file `config.php`

### 4. Selesai
- Website: `https://domainanda.com/`
- Admin Panel: `https://domainanda.com/admin/`

## Struktur Website

### Halaman Frontend
| Halaman | URL | Deskripsi |
|---------|-----|-----------|
| Beranda | `/` | Hero, layanan, portofolio, blog, CTA |
| Layanan | `/layanan` | Detail 6 layanan OJS |
| Portofolio | `/portofolio` | Galeri proyek dengan filter kategori |
| Blog | `/blog` | Artikel dan tips OJS |
| Harga | `/harga` | 3 paket harga + FAQ |
| Tentang | `/tentang` | Profil, visi misi, tim |
| Konsultasi | `/konsultasi` | Form konsultasi gratis |

### Dashboard Admin (`/admin/`)
| Fitur | Deskripsi |
|-------|-----------|
| Dashboard | Statistik, grafik konsultasi, aktivitas terbaru |
| Portofolio | CRUD portofolio (gambar, deskripsi, kategori) |
| Blog | CRUD artikel blog dengan editor HTML |
| Konsultasi (CRM) | Kelola form masuk, track status, follow-up, prioritas |
| Pengaturan | Ubah nama situs, warna, logo, favicon, password admin |
| Export | Export data konsultasi ke CSV dengan filter |

### Status CRM Konsultasi
```
Baru → Dihubungi → Follow Up → Negosiasi → Closed Won / Closed Lost
```

## Fitur Utama

- **Responsif** - Optimal di semua perangkat (mobile, tablet, desktop)
- **Kustomisasi Warna** - Ubah warna tema dari admin panel (color picker)
- **Upload Logo & Favicon** - Ganti branding dari admin panel
- **CRM Konsultasi** - Track progress konsultasi dari awal sampai closing
- **Export CSV** - Download data konsultasi dengan filter tanggal & status
- **SEO Ready** - Meta tags, clean URL, structured content
- **Keamanan** - CSRF protection, prepared statements, password hashing
- **Tanpa Dependencies** - Tidak perlu npm/composer, semua via CDN

## Catatan untuk Shared Hosting

### Jika mod_rewrite Tidak Aktif
Gunakan format URL dengan query string:
- `index.php?page=layanan`
- `index.php?page=portofolio`
- `admin/index.php?page=dashboard`

### Permission
```
config.php → 644
assets/uploads/ → 755 (recursive)
```

### Troubleshooting
- Jika halaman 404/500: pastikan `.htaccess` di-upload dan `AllowOverride All` aktif
- Jika gagal upload gambar: cek permission folder `assets/uploads/`
- Jika error database: cek kredensial di `config.php`

## Teknologi

- PHP 7.4+ (native, tanpa framework)
- MySQL / MariaDB
- Bootstrap 5.3.3
- Font Awesome 6.5
- Chart.js (admin dashboard)
- DataTables (admin tabel)
- Google Fonts (Plus Jakarta Sans + DM Serif Display)

## Lisensi

Hak Cipta © 2026. Seluruh hak dilindungi.
