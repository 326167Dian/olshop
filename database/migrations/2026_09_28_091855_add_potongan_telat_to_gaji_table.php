<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tarif potongan gaji per tingkat keterlambatan (dihitung dari batasTerlambat()
     * shift, bukan jadwal mentah -- lihat MasterShift::batasTerlambat() &
     * Absensi::hitungMenitTerlambat()). Dipakai InventoryGajiDetailController::
     * hitungPotonganTelatOtomatis().
     */
    public function up(): void
    {
        Schema::table('gaji', function (Blueprint $table) {
            $table->decimal('potongan_telat_15_30', 15, 2)->default(0)->after('rate_lembur');
            $table->decimal('potongan_telat_30_60', 15, 2)->default(0)->after('potongan_telat_15_30');
            $table->decimal('potongan_telat_60_lebih', 15, 2)->default(0)->after('potongan_telat_30_60');
        });
    }

    public function down(): void
    {
        Schema::table('gaji', function (Blueprint $table) {
            $table->dropColumn(['potongan_telat_15_30', 'potongan_telat_30_60', 'potongan_telat_60_lebih']);
        });
    }
};
