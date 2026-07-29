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
        Schema::create('kategoris', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('tipe')->index();
            $table->timestamps();
        });

        Schema::create('masa_manfaat_kategoris', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kategori_id')->index();
            $table->unsignedSmallInteger('masa_manfaat_tahun');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('masa_manfaat_kategoris');
        Schema::dropIfExists('kategoris');
    }
};
