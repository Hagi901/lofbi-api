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
        Schema::create('opname_sesis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ruangan_id')->index();
            $table->foreignId('admin_id')->index();
            $table->date('tanggal');
            $table->string('status')->default('selesai')->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('opname_sesis');
    }
};
