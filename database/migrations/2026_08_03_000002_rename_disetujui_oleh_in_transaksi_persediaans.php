<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Rename kolom disetujui_oleh → diputuskan_oleh di tabel transaksi_persediaans.
 *
 * Alasan: kolom ini diisi baik saat approve maupun tolak, sehingga nama
 * 'disetujui_oleh' secara semantik salah ketika diisi oleh aksi penolakan.
 * Nama 'diputuskan_oleh' lebih netral dan akurat.
 *
 * Sekaligus menambahkan FK constraint untuk kolom ini.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transaksi_persediaans', function (Blueprint $table) {
            $table->renameColumn('disetujui_oleh', 'diputuskan_oleh');
        });

        Schema::table('transaksi_persediaans', function (Blueprint $table) {
            $table->foreign('diputuskan_oleh')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('transaksi_persediaans', function (Blueprint $table) {
            $table->dropForeign(['diputuskan_oleh']);
            $table->renameColumn('diputuskan_oleh', 'disetujui_oleh');
        });
    }
};
