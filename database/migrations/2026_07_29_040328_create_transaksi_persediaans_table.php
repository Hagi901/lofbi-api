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
        Schema::create('transaksi_persediaans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('persediaan_id')->index();
            $table->string('jenis')->index();
            $table->unsignedInteger('jumlah');
            $table->date('tanggal');
            $table->string('unit_kerja_penerima')->nullable();
            $table->foreignId('diajukan_oleh')->nullable()->index();
            $table->string('status')->default('disetujui')->index();
            $table->foreignId('disetujui_oleh')->nullable()->index();
            $table->text('catatan_penolakan')->nullable();
            $table->timestamp('tanggal_keputusan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaksi_persediaans');
    }
};
