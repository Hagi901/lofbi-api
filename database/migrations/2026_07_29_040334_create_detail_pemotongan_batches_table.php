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
        Schema::create('detail_pemotongan_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaksi_persediaan_id')->index();
            $table->foreignId('batch_id')->index();
            $table->unsignedInteger('jumlah_diambil');
            $table->decimal('harga_satuan_saat_itu', 18, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_pemotongan_batches');
    }
};
