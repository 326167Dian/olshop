<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Modul Gaji Karyawan (adaptasi public/yasfigrup/masuk/modul/mod_gaji) --
     * satu baris per karyawan berisi gaji pokok harian & transportasi/ekstra
     * fooding harian, dipakai sebagai dasar hitung saat membuat Slip Gaji
     * (tabel gaji_detail).
     */
    public function up(): void
    {
        Schema::create('gaji', function (Blueprint $table) {
            $table->increments('id_gaji');
            $table->integer('id_admin')->unique();
            $table->decimal('gaji_harian', 15, 2)->default(0);
            $table->decimal('transportasi_harian', 15, 2)->default(0);
            $table->boolean('status_aktif')->default(true);

            $table->foreign('id_admin')->references('id_admin')->on('admin')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gaji');
    }
};
