<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Modul Kehadiran Pegawai -- definisi shift kerja (BUKAN `namashift`, yang
     * khusus modul Buka/Tutup Kasir dan hanya mencatat satu petugas per shift).
     */
    public function up(): void
    {
        Schema::create('master_shift', function (Blueprint $table) {
            $table->increments('id_shift');
            $table->string('nama_shift', 50);
            $table->time('jam_masuk');
            $table->time('jam_pulang');
            $table->integer('toleransi_telat')->default(15); // menit
            $table->boolean('status_aktif')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('master_shift');
    }
};
