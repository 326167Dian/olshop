<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Modul Kehadiran Pegawai -- penjadwalan shift per pegawai per tanggal.
     * Satu baris = satu shift di satu tanggal untuk satu pegawai (>1 shift/hari
     * didukung lewat >1 baris). Perlu approval pemilik sebelum dianggap sah
     * (dipakai sebagai acuan hitung status kehadiran & deteksi Alpha).
     */
    public function up(): void
    {
        Schema::create('jadwal_shift', function (Blueprint $table) {
            $table->increments('id_jadwal');
            $table->integer('id_admin'); // admin.id_admin signed, bukan unsigned
            $table->unsignedInteger('id_shift');
            $table->date('tanggal');
            $table->enum('status_approval', ['diajukan', 'disetujui', 'ditolak'])->default('diajukan');
            $table->integer('diajukan_oleh');
            $table->integer('disetujui_oleh')->nullable();
            $table->timestamp('disetujui_pada')->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->unique(['id_admin', 'id_shift', 'tanggal']);
            $table->foreign('id_admin')->references('id_admin')->on('admin')->restrictOnDelete();
            $table->foreign('id_shift')->references('id_shift')->on('master_shift')->restrictOnDelete();
            $table->foreign('diajukan_oleh')->references('id_admin')->on('admin')->restrictOnDelete();
            $table->foreign('disetujui_oleh')->references('id_admin')->on('admin')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jadwal_shift');
    }
};
