<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Kolom rekening bank/e-wallet admin, dipakai untuk info transfer di Slip Gaji
     * (modul Gaji Karyawan, adaptasi dari public/yasfigrup/masuk/modul/mod_gajidetail/
     * cetak_slip.php yang membaca admin.nama_bank/admin.rekening_bank).
     */
    public function up(): void
    {
        Schema::table('admin', function (Blueprint $table) {
            $table->string('nama_bank', 50)->nullable()->after('foto');
            $table->string('rekening_bank', 50)->nullable()->after('nama_bank');
        });
    }

    public function down(): void
    {
        Schema::table('admin', function (Blueprint $table) {
            $table->dropColumn(['nama_bank', 'rekening_bank']);
        });
    }
};
