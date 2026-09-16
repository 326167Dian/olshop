<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Modul Kehadiran Pegawai -- catatan kehadiran aktual (check-in/out).
     * `id_shift` sengaja disalin dari jadwal_shift saat baris ini dibuat
     * (bukan cuma dibaca lewat relasi id_jadwal) supaya perhitungan status
     * (Hadir/Terlambat) tetap konsisten meskipun jadwal aslinya diubah/dihapus
     * belakangan. `id_jadwal` nullable -- absen tanpa jadwal resmi tetap bisa
     * dicatat (mis. lembur dadakan / shift pengganti).
     */
    public function up(): void
    {
        Schema::create('absensi', function (Blueprint $table) {
            $table->increments('id_absensi');
            $table->integer('id_admin');
            $table->unsignedInteger('id_jadwal')->nullable();
            $table->unsignedInteger('id_shift')->nullable();
            $table->date('tanggal');
            $table->time('jam_masuk')->nullable();
            $table->time('jam_pulang')->nullable();
            $table->enum('status', ['hadir', 'terlambat', 'alpha', 'izin', 'cuti'])->default('hadir');
            $table->text('keterangan')->nullable();
            $table->enum('sumber', ['manual', 'self_service', 'sistem'])->default('manual');
            $table->integer('dicatat_oleh')->nullable();
            $table->timestamps();

            $table->unique(['id_admin', 'tanggal', 'id_shift']);
            $table->foreign('id_admin')->references('id_admin')->on('admin')->restrictOnDelete();
            $table->foreign('id_jadwal')->references('id_jadwal')->on('jadwal_shift')->nullOnDelete();
            $table->foreign('id_shift')->references('id_shift')->on('master_shift')->nullOnDelete();
            $table->foreign('dicatat_oleh')->references('id_admin')->on('admin')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('absensi');
    }
};
