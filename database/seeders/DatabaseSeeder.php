<?php

namespace Database\Seeders;

use App\Models\Aset;
use App\Models\BatchPersediaan;
use App\Models\JenisBarang;
use App\Models\Kategori;
use App\Models\MasaManfaatKategori;
use App\Models\Persediaan;
use App\Models\Ruangan;
use App\Models\TransaksiPersediaan;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // ── Users ─────────────────────────────────────────────────────────
        $admin = User::updateOrCreate(['email' => 'admin@lofbi.test'], [
            'name' => 'Admin LOFBI',
            'role' => 'admin',
            'password' => Hash::make('password'),
        ]);

        User::updateOrCreate(['email' => 'kasubbag@lofbi.test'], [
            'name' => 'Kasubbag LOFBI',
            'role' => 'kasubbag',
            'password' => Hash::make('password'),
        ]);

        // ── Ruangan ───────────────────────────────────────────────────────
        $ruangTU = Ruangan::updateOrCreate(['nama' => 'Ruang Tata Usaha'], ['gedung' => 'Gedung Utama']);
        $gudang = Ruangan::updateOrCreate(['nama' => 'Gudang Persediaan'], ['gedung' => 'Gedung Utama']);
        $ruangKepala = Ruangan::updateOrCreate(['nama' => 'Ruang Kepala'], ['gedung' => 'Gedung Utama']);

        // ── Kategori & Masa Manfaat ────────────────────────────────────────
        $atk = Kategori::updateOrCreate(['nama' => 'ATK'], ['tipe' => 'persediaan']);
        $rtg = Kategori::updateOrCreate(['nama' => 'Rumah Tangga'], ['tipe' => 'persediaan']);
        $elektronik = Kategori::updateOrCreate(['nama' => 'Elektronik'], ['tipe' => 'aset']);
        $furnitur = Kategori::updateOrCreate(['nama' => 'Furnitur'], ['tipe' => 'aset']);

        MasaManfaatKategori::updateOrCreate(['kategori_id' => $elektronik->id], ['masa_manfaat_tahun' => 4]);
        MasaManfaatKategori::updateOrCreate(['kategori_id' => $furnitur->id], ['masa_manfaat_tahun' => 8]);

        // ── Jenis Barang ───────────────────────────────────────────────────
        $laptop = JenisBarang::updateOrCreate(['nama_generik' => 'Laptop'], ['kategori_id' => $elektronik->id]);
        $printer = JenisBarang::updateOrCreate(['nama_generik' => 'Printer'], ['kategori_id' => $elektronik->id]);
        $meja = JenisBarang::updateOrCreate(['nama_generik' => 'Meja Kerja'], ['kategori_id' => $furnitur->id]);
        $kursi = JenisBarang::updateOrCreate(['nama_generik' => 'Kursi'], ['kategori_id' => $furnitur->id]);
        $pulpen = JenisBarang::updateOrCreate(['nama_generik' => 'Pulpen'], ['kategori_id' => $atk->id]);
        $kertas = JenisBarang::updateOrCreate(['nama_generik' => 'Kertas A4'], ['kategori_id' => $atk->id]);
        $tinta = JenisBarang::updateOrCreate(['nama_generik' => 'Tinta Printer'], ['kategori_id' => $rtg->id]);

        // ── Aset Sample ────────────────────────────────────────────────────
        Aset::updateOrCreate(['kode_aset' => 'ELK-LAP-001'], [
            'jenis_barang_id' => $laptop->id,
            'merk' => 'Lenovo',
            'model' => 'ThinkPad E14',
            'kondisi' => 'baik',
            'ruangan_id' => $ruangTU->id,
            'nilai_perolehan' => 12_000_000,
            'tanggal_perolehan' => '2023-01-15',
            'akumulasi_penyusutan' => 3_000_000,
            'nilai_buku' => 9_000_000,
        ]);
        Aset::updateOrCreate(['kode_aset' => 'ELK-LAP-002'], [
            'jenis_barang_id' => $laptop->id,
            'merk' => 'ASUS',
            'model' => 'ExpertBook B1',
            'kondisi' => 'rusak_ringan',
            'ruangan_id' => $ruangKepala->id,
            'nilai_perolehan' => 10_500_000,
            'tanggal_perolehan' => '2022-07-01',
            'akumulasi_penyusutan' => 5_250_000,
            'nilai_buku' => 5_250_000,
        ]);
        Aset::updateOrCreate(['kode_aset' => 'ELK-PRN-001'], [
            'jenis_barang_id' => $printer->id,
            'merk' => 'Canon',
            'model' => 'PIXMA G2020',
            'kondisi' => 'baik',
            'ruangan_id' => $ruangTU->id,
            'nilai_perolehan' => 2_500_000,
            'tanggal_perolehan' => '2024-03-10',
            'akumulasi_penyusutan' => 312_500,
            'nilai_buku' => 2_187_500,
        ]);
        Aset::updateOrCreate(['kode_aset' => 'FUR-MJK-001'], [
            'jenis_barang_id' => $meja->id,
            'merk' => 'Olympic',
            'model' => null,
            'kondisi' => 'baik',
            'ruangan_id' => $ruangTU->id,
            'nilai_perolehan' => 1_800_000,
            'tanggal_perolehan' => '2021-01-01',
            'akumulasi_penyusutan' => 675_000,
            'nilai_buku' => 1_125_000,
        ]);
        Aset::updateOrCreate(['kode_aset' => 'FUR-MJK-002'], [
            'jenis_barang_id' => $meja->id,
            'merk' => 'Olympic',
            'model' => null,
            'kondisi' => 'rusak_berat',
            'ruangan_id' => $ruangKepala->id,
            'nilai_perolehan' => 1_800_000,
            'tanggal_perolehan' => '2019-01-01',
            'akumulasi_penyusutan' => 1_800_000,
            'nilai_buku' => 0,
        ]);

        // ── Persediaan & Batch ─────────────────────────────────────────────
        $persediaanPulpen = Persediaan::updateOrCreate(
            ['jenis_barang_id' => $pulpen->id, 'merk' => null],
            ['satuan' => 'pcs', 'stok_minimum' => 10, 'ruangan_id' => $gudang->id]
        );
        BatchPersediaan::updateOrCreate(
            ['persediaan_id' => $persediaanPulpen->id, 'no_batch' => 1],
            ['tanggal_masuk' => '2026-01-10', 'jumlah_masuk' => 50, 'harga_satuan' => 3_000, 'sisa_stok' => 20]
        );
        BatchPersediaan::updateOrCreate(
            ['persediaan_id' => $persediaanPulpen->id, 'no_batch' => 2],
            ['tanggal_masuk' => '2026-04-15', 'jumlah_masuk' => 100, 'harga_satuan' => 3_200, 'sisa_stok' => 100]
        );

        $persediaanKertas = Persediaan::updateOrCreate(
            ['jenis_barang_id' => $kertas->id, 'merk' => 'Sinar Dunia'],
            ['satuan' => 'rim', 'stok_minimum' => 5, 'ruangan_id' => $gudang->id]
        );
        BatchPersediaan::updateOrCreate(
            ['persediaan_id' => $persediaanKertas->id, 'no_batch' => 1],
            ['tanggal_masuk' => '2026-03-01', 'jumlah_masuk' => 20, 'harga_satuan' => 45_000, 'sisa_stok' => 8]
        );

        $persediaanTinta = Persediaan::updateOrCreate(
            ['jenis_barang_id' => $tinta->id, 'merk' => 'Canon'],
            ['satuan' => 'botol', 'stok_minimum' => 2, 'ruangan_id' => $gudang->id]
        );
        BatchPersediaan::updateOrCreate(
            ['persediaan_id' => $persediaanTinta->id, 'no_batch' => 1],
            ['tanggal_masuk' => '2026-05-20', 'jumlah_masuk' => 6, 'harga_satuan' => 95_000, 'sisa_stok' => 1]
            // sisa_stok=1 < stok_minimum=2 → akan muncul sebagai alert stok menipis di dashboard
        );

        // ── Pengajuan Menunggu Approval ────────────────────────────────────
        // Satu pengajuan contoh berstatus 'menunggu' agar dashboard alert terisi
        TransaksiPersediaan::updateOrCreate(
            [
                'persediaan_id' => $persediaanPulpen->id,
                'jenis' => 'keluar',
                'status' => 'menunggu',
            ],
            [
                'jumlah' => 15,
                'tanggal' => now()->toDateString(),
                'unit_kerja_penerima' => 'Seksi Kepegawaian',
                'diajukan_oleh' => $admin->id,
            ]
        );
    }
}
