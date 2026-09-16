<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tarif lembur per jam (Rupiah), dipakai modul Kehadiran Pegawai untuk
     * mengonversi `lembur.jam_lembur` (jam) jadi nominal saat ditarik ke
     * `gaji_detail.lembur` (Rupiah) -- lihat InventoryGajiDetailController.
     */
    public function up(): void
    {
        Schema::table('gaji', function (Blueprint $table) {
            $table->decimal('rate_lembur', 15, 2)->default(0)->after('transportasi_harian');
        });
    }

    public function down(): void
    {
        Schema::table('gaji', function (Blueprint $table) {
            $table->dropColumn('rate_lembur');
        });
    }
};
