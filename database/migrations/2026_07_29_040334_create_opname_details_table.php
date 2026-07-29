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
        Schema::create('opname_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('opname_sesi_id')->index();
            $table->foreignId('aset_id')->nullable()->index();
            $table->foreignId('persediaan_id')->nullable()->index();
            $table->string('kondisi_aktual')->nullable();
            $table->unsignedInteger('jumlah_aktual')->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('opname_details');
    }
};
