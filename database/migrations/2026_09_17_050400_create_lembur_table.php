<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Modul Kehadiran Pegawai -- rekap jam lembur per pegawai. Baris yang
     * `status_approval='disetujui'` dan belum `ditarik_ke_gaji` menjadi sumber
     * auto-isi kolom `gaji_detail.lembur` saat Slip Gaji dibuat (lihat
     * InventoryGajiDetailController) -- begitu ditarik, `ditarik_ke_gaji`
     * dan `id_gaji_detail` diisi supaya tidak ikut terhitung dobel di slip
     * periode berikutnya.
     */
    public function up(): void
    {
        Schema::create('lembur', function (Blueprint $table) {
            $table->increments('id_lembur');
            $table->integer('id_admin');
            $table->unsignedInteger('id_absensi')->nullable();
            $table->date('tanggal');
            $table->decimal('jam_lembur', 5, 2)->default(0);
            $table->enum('sumber', ['otomatis', 'manual'])->default('manual');
            $table->enum('status_approval', ['diajukan', 'disetujui', 'ditolak'])->default('diajukan');
            $table->boolean('ditarik_ke_gaji')->default(false);
            $table->unsignedInteger('id_gaji_detail')->nullable();
            $table->text('keterangan')->nullable();
            $table->integer('dicatat_oleh')->nullable();
            $table->timestamps();

            $table->foreign('id_admin')->references('id_admin')->on('admin')->restrictOnDelete();
            $table->foreign('id_absensi')->references('id_absensi')->on('absensi')->nullOnDelete();
            $table->foreign('id_gaji_detail')->references('id_gaji_detail')->on('gaji_detail')->nullOnDelete();
            $table->foreign('dicatat_oleh')->references('id_admin')->on('admin')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lembur');
    }
};
