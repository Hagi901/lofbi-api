# 📦 LOFBI API — REST API Inventaris & Persediaan (BMN)

![Laravel Version](https://img.shields.io/badge/Laravel-11-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)
![PHP Version](https://img.shields.io/badge/PHP-%5E8.2-777BB4?style=for-the-badge&logo=php&logoColor=white)
![Database](https://img.shields.io/badge/Database-MySQL%20%7C%20SQLite-4479A1?style=for-the-badge&logo=mysql&logoColor=white)
![License](https://img.shields.io/badge/License-MIT-green?style=for-the-badge)

Backend REST API untuk **Sistem Layanan Operasional & Form BMN / Inventarisasi (LOFBI)**. Aplikasi ini mengelola aset fisik inventaris (dengan kalkulasi penyusutan otomatis), persediaan barang habis pakai (dengan metode pemotongan stok FIFO), stok opname fisik per ruangan, serta menyajikan dashboard statistik dan laporan resmi (BAOP, DBR, Nilai Buku).

---

## ✨ Fitur Utama

- 🔑 **Autentikasi Bearer Token (Sanctum)** — Keamanan endpoint dengan sistem perizinan berbasis peran (*Role-Based Access Control*).
- 👥 **Multi Role User**:
  - `admin`: Input aset, persediaan, barang masuk, buat pengajuan keluar, dan opname fisik.
  - `kasubbag`: Monitoring dashboard, approval/penolakan pengajuan barang keluar, dan laporan.
- 🏢 **Manajemen Aset Inventaris**:
  - Pengelompokan aset per jenis barang dan unit detail.
  - Penghitungan penyusutan metode garis lurus (*straight-line*) otomatis per semester.
- 📦 **Manajemen Persediaan & Batch FIFO**:
  - Pencatatan barang masuk per batch (harga & tanggal per perolehan).
  - Pemotongan stok otomatis metode **FIFO (First In, First Out)** dari batch paling awal saat disetujui Kasubbag.
  - Validasi kecukupan stok secara real-time.
- 📋 **Stok Opname Fisik**:
  - Verifikasi kondisi fisik barang aktual per ruangan dan pencatatan riwayat sesi opname.
- 📊 **Dashboard & Laporan**:
  - Statistik total aset, nilai buku, barang rusak, alert stok menipis, dan pengajuan *pending*.
  - Laporan Berita Acara Opname (BAOP), Daftar Barang Ruangan (DBR), Nilai Buku Aset, serta Export CSV berstandar RFC 4180.

---

## 🛠️ Persyaratan Sistem

- PHP `>= 8.2`
- Composer `>= 2.0`
- MySQL / MariaDB (via XAMPP) atau SQLite
- Extension PHP: `OpenSSL`, `PDO`, `Mbstring`, `Tokenizer`, `XML`, `Ctype`, `JSON`, `BCMath`

---

## 🚀 Panduan Instalasi (Local Development)

### 1. Clone Repository
```bash
git clone https://github.com/Hagi901/lofbi-api.git
cd lofbi-api
```

### 2. Install Dependency PHP
```bash
composer install
```

### 3. Buat File Konfigurasi `.env`
```bash
copy .env.example .env
php artisan key:generate
```

### 4. Konfigurasi Database di `.env` (XAMPP / MySQL)
Buat database bernama **`lofbi_db`** di phpMyAdmin (`http://localhost/phpmyadmin`), lalu buka file `.env` dan atur:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=lofbi_db
DB_USERNAME=root
DB_PASSWORD=
```

### 5. Jalankan Migrasi Database & Seeder Data Demo
```bash
php artisan migrate:fresh --seed
```

### 6. Jalankan Server API
```bash
php artisan serve
```
Server REST API aktif di: **`http://127.0.0.1:8000/api`**

---

## 🔐 Akun Demo (Seeder)

| Role | Email | Password | Hak Akses Utama |
|---|---|---|---|
| **Admin** | `admin@lofbi.test` | `password` | Full Input (Aset, Persediaan, Opname, Pengajuan) |
| **Kasubbag** | `kasubbag@lofbi.test` | `password` | Approval FIFO, Monitoring Dashboard, Laporan |

---

## 📑 Daftar Endpoint Utama (REST API)

| Method | Endpoint | Role | Deskripsi |
|---|---|---|---|
| `POST` | `/api/login` | Public | Login user & dapatkan token Bearer |
| `POST` | `/api/logout` | Auth | Revoke token session |
| `GET` | `/api/me` | Auth | Profil user terautentikasi |
| `GET` | `/api/dashboard/summary` | Auth | Ringkasan statistik & alert dashboard |
| `GET` | `/api/aset/ringkas` | Auth | Ringkasan aset per jenis barang (Paginated) |
| `GET` | `/api/aset/jenis/{id}/unit` | Auth | Detail unit aset per jenis barang |
| `POST` | `/api/aset` | Admin | Tambah unit aset baru |
| `GET` | `/api/persediaan/ringkas` | Auth | Ringkasan persediaan & sisa stok total |
| `POST` | `/api/persediaan/{id}/barang-masuk` | Admin | Input barang masuk (buat batch baru) |
| `POST` | `/api/persediaan/{id}/pengajuan-keluar` | Admin | Buat pengajuan barang keluar |
| `GET` | `/api/persediaan/pengajuan` | Auth | Daftar pengajuan barang keluar |
| `POST` | `/api/persediaan/pengajuan/{id}/setujui` | **Kasubbag** | Approval pengajuan & potong stok FIFO |
| `POST` | `/api/persediaan/pengajuan/{id}/tolak` | **Kasubbag** | Tolak pengajuan barang keluar (wajib isi alasan) |
| `GET` | `/api/opname/ruangan/{id}` | Admin | Referensi aset & persediaan di ruangan |
| `POST` | `/api/opname` | Admin | Simpan hasil pemeriksaan opname fisik |
| `GET` | `/api/laporan/baop` | Auth | Laporan Berita Acara Opname |
| `GET` | `/api/laporan/dbr` | Auth | Laporan Daftar Barang Ruangan |
| `GET` | `/api/laporan/nilai-buku` | Auth | Laporan Rekap Nilai Buku Aset |
| `GET` | `/api/laporan/export?jenis=dbr&format=csv` | Auth | Export data laporan ke format CSV |

---

## 🧪 Pengujian API (Postman Collection)

File **[`LOFBI_API.postman_collection.json`](LOFBI_API.postman_collection.json)** telah tersedia di root repository ini.

1. Buka aplikasi **Postman** / Bruno / Insomnia.
2. Import file `LOFBI_API.postman_collection.json`.
3. Jalankan request **`Login Admin`** atau **`Login Kasubbag`**. Token Bearer akan otomatis tersimpan untuk request berikutnya.

---

## 📐 Algoritma & Logika Bisnis

### 1. Metode FIFO Persediaan
```text
Batch #1 (Masuk 10 Jan) -> Sisa: 20 pcs @ Rp 3.000
Batch #2 (Masuk 15 Apr) -> Sisa: 100 pcs @ Rp 3.200

Pengajuan Keluar disetujui: 30 pcs
- 20 pcs diambil dari Batch #1 (Sisa Batch #1 -> 0)
- 10 pcs diambil dari Batch #2 (Sisa Batch #2 -> 90)
```

### 2. Penyusutan Aset (Garis Lurus per Semester)
```text
Penyusutan / Tahun = Nilai Perolehan / Masa Manfaat (Tahun)
Penyusutan / Semester = Penyusutan / Tahun / 2
Nilai Buku = Nilai Perolehan - Akumulasi Penyusutan
```
Command scheduler: `php artisan lofbi:hitung-penyusutan` (Otomatis berjalan tiap 1 Jan & 1 Juli).

---

## 📄 Lisensi

Project ini dirilis di bawah lisensi [MIT License](LICENSE).
