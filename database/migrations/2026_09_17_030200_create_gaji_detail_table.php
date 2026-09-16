<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Modul Slip Gaji (adaptasi public/yasfigrup/masuk/modul/mod_gajidetail) --
     * satu baris per periode (bulan) per karyawan. `total` adalah kolom generated
     * (STORED) mengikuti perilaku legacy: take home pay = jumlah komponen
     * penghasilan dikurangi pinjaman/kasbon.
     */
    public function up(): void
    {
        Schema::create('gaji_detail', function (Blueprint $table) {
            $table->increments('id_gaji_detail');
            $table->unsignedInteger('id_gaji'); // gaji.id_gaji dibuat dari increments() => unsigned
            $table->integer('id_admin'); // admin.id_admin adalah int signed, bukan unsigned
            $table->char('periode_bulan', 7); // format YYYY-MM
            $table->date('tgl_awal');
            $table->date('tgl_akhir');
            $table->integer('jumlah_hari')->default(0);
            $table->decimal('gaji_pokok', 15, 2)->default(0);
            $table->decimal('transportasi', 15, 2)->default(0);
            $table->decimal('konsumsi', 15, 2)->default(0); // dinonaktifkan sementara di form, lihat controller
            $table->decimal('lembur', 15, 2)->default(0);
            $table->decimal('komisi', 15, 2)->default(0);
            $table->decimal('fee_malam', 15, 2)->default(0);
            $table->decimal('pinjaman', 15, 2)->default(0);
            $table->decimal('total', 15, 2)
                ->storedAs('gaji_pokok + transportasi + lembur + komisi + fee_malam - pinjaman');
            $table->integer('dibuat_oleh');
            $table->integer('disetujui_oleh')->nullable();

            $table->unique(['id_admin', 'periode_bulan']);
            $table->foreign('id_gaji')->references('id_gaji')->on('gaji')->restrictOnDelete();
            $table->foreign('id_admin')->references('id_admin')->on('admin')->restrictOnDelete();
            $table->foreign('dibuat_oleh')->references('id_admin')->on('admin')->restrictOnDelete();
            $table->foreign('disetujui_oleh')->references('id_admin')->on('admin')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gaji_detail');
    }
};
