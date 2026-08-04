<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Menambahkan Foreign Key Constraints yang proper ke semua tabel.
 * Sebelumnya hanya ada ->index() tanpa ->constrained(), sehingga
 * referential integrity tidak ditegakkan di level database.
 */
return new class extends Migration
{
    public function up(): void
    {
        // jenis_barangs.kategori_id → kategoris
        Schema::table('jenis_barangs', function (Blueprint $table) {
            $table->foreign('kategori_id')->references('id')->on('kategoris')->cascadeOnDelete();
        });

        // asets
        Schema::table('asets', function (Blueprint $table) {
            $table->foreign('jenis_barang_id')->references('id')->on('jenis_barangs')->restrictOnDelete();
            $table->foreign('ruangan_id')->references('id')->on('ruangans')->nullOnDelete();
        });

        // persediaans
        Schema::table('persediaans', function (Blueprint $table) {
            $table->foreign('jenis_barang_id')->references('id')->on('jenis_barangs')->restrictOnDelete();
            $table->foreign('ruangan_id')->references('id')->on('ruangans')->nullOnDelete();
        });

        // batch_persediaans
        Schema::table('batch_persediaans', function (Blueprint $table) {
            $table->foreign('persediaan_id')->references('id')->on('persediaans')->cascadeOnDelete();
        });

        // transaksi_persediaans
        Schema::table('transaksi_persediaans', function (Blueprint $table) {
            $table->foreign('persediaan_id')->references('id')->on('persediaans')->restrictOnDelete();
            $table->foreign('diajukan_oleh')->references('id')->on('users')->nullOnDelete();
        });

        // detail_pemotongan_batches
        Schema::table('detail_pemotongan_batches', function (Blueprint $table) {
            $table->foreign('transaksi_persediaan_id')->references('id')->on('transaksi_persediaans')->cascadeOnDelete();
            $table->foreign('batch_id')->references('id')->on('batch_persediaans')->restrictOnDelete();
        });

        // opname_sesis
        Schema::table('opname_sesis', function (Blueprint $table) {
            $table->foreign('ruangan_id')->references('id')->on('ruangans')->restrictOnDelete();
            $table->foreign('admin_id')->references('id')->on('users')->restrictOnDelete();
        });

        // opname_details
        Schema::table('opname_details', function (Blueprint $table) {
            $table->foreign('opname_sesi_id')->references('id')->on('opname_sesis')->cascadeOnDelete();
            $table->foreign('aset_id')->references('id')->on('asets')->nullOnDelete();
            $table->foreign('persediaan_id')->references('id')->on('persediaans')->nullOnDelete();
        });

        // riwayat_asets
        Schema::table('riwayat_asets', function (Blueprint $table) {
            $table->foreign('aset_id')->references('id')->on('asets')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('riwayat_asets', fn (Blueprint $t) => $t->dropForeign(['aset_id']));
        Schema::table('opname_details', fn (Blueprint $t) => $t->dropForeign(['opname_sesi_id', 'aset_id', 'persediaan_id']));
        Schema::table('opname_sesis', fn (Blueprint $t) => $t->dropForeign(['ruangan_id', 'admin_id']));
        Schema::table('detail_pemotongan_batches', fn (Blueprint $t) => $t->dropForeign(['transaksi_persediaan_id', 'batch_id']));
        Schema::table('transaksi_persediaans', fn (Blueprint $t) => $t->dropForeign(['persediaan_id', 'diajukan_oleh']));
        Schema::table('batch_persediaans', fn (Blueprint $t) => $t->dropForeign(['persediaan_id']));
        Schema::table('persediaans', fn (Blueprint $t) => $t->dropForeign(['jenis_barang_id', 'ruangan_id']));
        Schema::table('asets', fn (Blueprint $t) => $t->dropForeign(['jenis_barang_id', 'ruangan_id']));
        Schema::table('jenis_barangs', fn (Blueprint $t) => $t->dropForeign(['kategori_id']));
    }
};
