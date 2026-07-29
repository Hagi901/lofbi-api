<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('asets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jenis_barang_id')->index();
            $table->string('kode_aset')->unique();
            $table->string('merk')->nullable();
            $table->string('model')->nullable();
            $table->string('kondisi')->default('baik')->index();
            $table->foreignId('ruangan_id')->nullable()->index();
            $table->decimal('nilai_perolehan', 18, 2)->default(0);
            $table->date('tanggal_perolehan')->nullable();
            $table->decimal('akumulasi_penyusutan', 18, 2)->default(0);
            $table->decimal('nilai_buku', 18, 2)->default(0);
            $table->string('terakhir_dihitung_semester')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asets');
    }
};
