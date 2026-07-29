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
        Schema::create('batch_persediaans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('persediaan_id')->index();
            $table->unsignedInteger('no_batch');
            $table->date('tanggal_masuk');
            $table->unsignedInteger('jumlah_masuk');
            $table->decimal('harga_satuan', 18, 2);
            $table->unsignedInteger('sisa_stok');
            $table->timestamps();
            $table->unique(['persediaan_id', 'no_batch']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('batch_persediaans');
    }
};
