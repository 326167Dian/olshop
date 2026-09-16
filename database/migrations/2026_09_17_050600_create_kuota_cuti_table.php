<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Modul Kehadiran Pegawai -- kuota cuti tahunan per pegawai per tahun.
     * Sisa kuota DIHITUNG saat dibaca (kuota_hari - SUM jumlah_hari cuti
     * jenis 'tahunan' berstatus disetujui pada tahun itu), bukan disimpan
     * sebagai kolom terpisah -- lihat KuotaCuti::sisaUntuk().
     */
    public function up(): void
    {
        Schema::create('kuota_cuti', function (Blueprint $table) {
            $table->increments('id_kuota');
            $table->integer('id_admin');
            $table->year('tahun');
            $table->integer('kuota_hari')->default(12);
            $table->timestamps();

            $table->unique(['id_admin', 'tahun']);
            $table->foreign('id_admin')->references('id_admin')->on('admin')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kuota_cuti');
    }
};
