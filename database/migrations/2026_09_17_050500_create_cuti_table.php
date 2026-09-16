<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Modul Kehadiran Pegawai -- pengajuan cuti. `jumlah_hari` dihitung &
     * disimpan saat submit (selisih tanggal_mulai-tanggal_selesai inklusif),
     * dipakai untuk hitung sisa kuota tahunan (lihat kuota_cuti) tanpa perlu
     * hitung ulang selisih tanggal setiap kali.
     */
    public function up(): void
    {
        Schema::create('cuti', function (Blueprint $table) {
            $table->increments('id_cuti');
            $table->integer('id_admin');
            $table->enum('jenis_cuti', ['tahunan', 'sakit', 'izin', 'lainnya'])->default('tahunan');
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->integer('jumlah_hari');
            $table->text('alasan');
            $table->enum('status', ['diajukan', 'disetujui', 'ditolak'])->default('diajukan');
            $table->integer('disetujui_oleh')->nullable();
            $table->timestamp('disetujui_pada')->nullable();
            $table->text('catatan_approval')->nullable();
            $table->timestamps();

            $table->foreign('id_admin')->references('id_admin')->on('admin')->restrictOnDelete();
            $table->foreign('disetujui_oleh')->references('id_admin')->on('admin')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cuti');
    }
};
