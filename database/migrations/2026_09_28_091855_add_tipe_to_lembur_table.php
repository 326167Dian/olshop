<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Bedakan lembur "per_jam" (shift tunggal pulang lewat jadwalnya sendiri --
     * dihitung jam x rate_lembur, kasus lama) dari "per_shift" (shift tambahan/
     * ke-2+ di hari yang sama -- flat 1x gaji_harian, tanpa peduli berapa jam
     * kerjanya). Lihat InventoryKehadiranController::checkout() dan
     * InventoryGajiDetailController::hitungLemburOtomatis().
     */
    public function up(): void
    {
        Schema::table('lembur', function (Blueprint $table) {
            $table->enum('tipe', ['per_jam', 'per_shift'])->default('per_jam')->after('jam_lembur');
        });
    }

    public function down(): void
    {
        Schema::table('lembur', function (Blueprint $table) {
            $table->dropColumn('tipe');
        });
    }
};
