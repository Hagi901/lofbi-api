# Penjelasan Backend LOFBI

Dokumen ini menjelaskan apa saja yang sudah dibuat di project Laravel ini agar alur backend LOFBI lebih mudah dipahami.

## 1. Status Pekerjaan

Yang sudah dibuat saat ini adalah backend saja, yaitu REST API Laravel untuk dipakai oleh frontend.

Frontend belum dibuat di repository ini. Nantinya frontend dari rekan tim bisa mengambil data dari endpoint `/api/...` menggunakan JSON.

## 2. Fungsi Backend Ini

Backend LOFBI berfungsi untuk:

- Login dan logout user.
- Mengelola data aset inventaris.
- Mengelola data persediaan barang habis pakai.
- Mencatat barang masuk per batch.
- Membuat pengajuan barang keluar.
- Memproses approval Kasubbag.
- Memotong stok otomatis dengan metode FIFO.
- Mencatat hasil opname fisik.
- Menyediakan data dashboard.
- Menyediakan data laporan BAOP, DBR, dan nilai buku.
- Menghitung penyusutan aset per semester.

## 3. Akun Demo

Seeder sudah membuat dua akun untuk percobaan:

```text
Admin
Email    : admin@lofbi.test
Password : password
Role     : admin

Kasubbag
Email    : kasubbag@lofbi.test
Password : password
Role     : kasubbag
```

Admin digunakan untuk input aset, input persediaan, barang masuk, opname, dan pengajuan barang keluar.

Kasubbag digunakan untuk melihat data dan menyetujui atau menolak pengajuan barang keluar.

## 4. Cara Menjalankan

Jalankan migration:

```bash
php artisan migrate
```

Isi data awal:

```bash
php artisan db:seed
```

Jalankan server:

```bash
php artisan serve
```

Alamat API lokal:

```text
http://127.0.0.1:8000/api
```

## 5. Cara Login API

Request:

```http
POST /api/login
```

Body JSON:

```json
{
  "email": "admin@lofbi.test",
  "password": "password"
}
```

Response akan berisi `access_token`.

Untuk endpoint lain, gunakan header:

```http
Authorization: Bearer TOKEN_DARI_LOGIN
```

## 6. Modul Aset

Endpoint utama:

```text
GET    /api/aset/ringkas
GET    /api/aset/jenis/{id}/unit
POST   /api/aset
GET    /api/aset/{id}
PUT    /api/aset/{id}
DELETE /api/aset/{id}
GET    /api/aset/{id}/riwayat
```

Data aset menyimpan:

- Jenis barang.
- Kode aset.
- Merk dan model.
- Kondisi.
- Lokasi ruangan.
- Nilai perolehan.
- Akumulasi penyusutan.
- Nilai buku.

Endpoint `/api/aset/ringkas` menampilkan data aset secara grouped berdasarkan jenis barang, misalnya Laptop, Meja, Kursi.

Endpoint `/api/aset/jenis/{id}/unit` menampilkan detail unit per jenis barang.

## 7. Penyusutan Aset

Penyusutan memakai metode garis lurus:

```text
Penyusutan per tahun = nilai perolehan / masa manfaat
Penyusutan per semester = penyusutan per tahun / 2
Nilai buku = nilai perolehan - akumulasi penyusutan
```

Command untuk menghitung penyusutan:

```bash
php artisan lofbi:hitung-penyusutan
```

Command ini juga dijadwalkan untuk berjalan setiap awal semester:

- 1 Januari.
- 1 Juli.

## 8. Modul Persediaan

Endpoint utama:

```text
GET  /api/persediaan/ringkas
GET  /api/persediaan/jenis/{id}/detail
POST /api/persediaan
PUT  /api/persediaan/{id}
POST /api/persediaan/{id}/barang-masuk
POST /api/persediaan/{id}/pengajuan-keluar
GET  /api/persediaan/pengajuan
POST /api/persediaan/pengajuan/{id}/setujui
POST /api/persediaan/pengajuan/{id}/tolak
GET  /api/persediaan/{id}/batch
```

Barang masuk akan otomatis membuat batch baru dengan `no_batch` berurutan.

Contoh:

```text
Pulpen batch 1 masuk 10 pcs harga 1.000
Pulpen batch 2 masuk 20 pcs harga 1.200
```

Jika ada barang keluar 15 pcs, sistem akan mengambil:

```text
10 pcs dari batch 1
5 pcs dari batch 2
```

Inilah metode FIFO: First In, First Out.

## 9. Approval Barang Keluar

Alurnya:

1. Admin membuat pengajuan barang keluar.
2. Status pengajuan menjadi `menunggu`.
3. Kasubbag membuka daftar pengajuan.
4. Kasubbag memilih setujui atau tolak.
5. Jika disetujui, stok otomatis dipotong dengan FIFO.
6. Jika ditolak, catatan penolakan wajib diisi.

Hanya user dengan role `kasubbag` yang bisa menyetujui atau menolak pengajuan.

## 10. Modul Opname

Endpoint utama:

```text
GET  /api/opname/ruangan/{id}
POST /api/opname
GET  /api/opname/riwayat
```

Endpoint `/api/opname/ruangan/{id}` mengambil daftar barang yang seharusnya ada di ruangan tersebut.

Endpoint `/api/opname` menyimpan hasil pemeriksaan fisik lapangan.

## 11. Modul Laporan

Endpoint utama:

```text
GET /api/laporan/baop
GET /api/laporan/dbr
GET /api/laporan/nilai-buku
GET /api/laporan/export
```

Saat ini laporan dikembalikan dalam bentuk JSON atau CSV sederhana.

PDF server-side belum dibuat. Nantinya PDF bisa dibuat di backend menggunakan package PDF, atau di frontend dari data JSON.

## 12. Dashboard

Endpoint:

```text
GET /api/dashboard/summary
```

Data yang ditampilkan:

- Total aset.
- Total nilai buku.
- Total jenis persediaan.
- Jumlah barang rusak.
- Jumlah stok menipis.
- Jumlah pengajuan menunggu approval.
- Distribusi kondisi barang.
- Distribusi lokasi.

## 13. Master Data

Endpoint:

```text
GET /api/ruangan
GET /api/kategori
```

Master data digunakan frontend untuk pilihan dropdown, filter, dan input form.

## 14. Catatan Tentang Sanctum

PRD meminta Laravel Sanctum.

Saat dicoba dipasang, Composer gagal mengunduh Sanctum karena koneksi Packagist timeout. Karena itu, untuk sementara backend memakai token Bearer internal dengan tabel `personal_access_tokens`.

Dari sisi frontend, cara pakainya tetap sama:

```http
Authorization: Bearer TOKEN
```

Jika nanti koneksi Composer sudah lancar, backend bisa dipindahkan ke Sanctum resmi.

## 15. File Penting

```text
routes/api.php
```

Berisi daftar endpoint API.

```text
app/Http/Controllers/Api
```

Berisi logic per modul, seperti Auth, Aset, Persediaan, Opname, Laporan, dan Dashboard.

```text
app/Models
```

Berisi model database dan relasi antar tabel.

```text
database/migrations
```

Berisi struktur tabel database.

```text
database/seeders/DatabaseSeeder.php
```

Berisi akun demo dan data awal.

```text
tests/Feature/LofbiApiTest.php
```

Berisi test login dan test FIFO approval.

## 16. Kesimpulan

Project ini sekarang sudah memiliki fondasi backend LOFBI yang bisa dipakai oleh frontend.

Tahap berikutnya adalah membuat atau menghubungkan frontend agar user bisa login, melihat dashboard, input aset, input persediaan, melakukan opname, dan memproses approval melalui tampilan web.
