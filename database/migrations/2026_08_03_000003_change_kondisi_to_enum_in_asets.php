<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Mengubah kolom 'kondisi' di tabel asets dari string biasa
 * menjadi enum dengan nilai yang terdefinisi ketat.
 *
 * Sebelumnya nilai hanya divalidasi di level aplikasi,
 * sehingga input langsung ke DB (via tinker/seeder/bug) bisa lolos.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('asets', function (Blueprint $table) {
            $table->enum('kondisi', ['baik', 'rusak_ringan', 'rusak_berat'])
                ->default('baik')
                ->change();
        });
    }

    public function down(): void
    {
        Schema::table('asets', function (Blueprint $table) {
            $table->string('kondisi')->default('baik')->change();
        });
    }
};
