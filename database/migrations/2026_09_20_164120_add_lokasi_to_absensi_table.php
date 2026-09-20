<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('absensi', function (Blueprint $table) {
            // Jejak lokasi saat check-in dikirim (untuk audit/verifikasi, bukan cuma
            // hasil lolos/tidaknya validasi radius). Nullable karena absensi manual
            // (input pemilik/petugas) tidak melalui geolocation sama sekali.
            $table->decimal('lat', 10, 7)->nullable()->after('jam_pulang');
            $table->decimal('lng', 10, 7)->nullable()->after('lat');
            $table->decimal('jarak_meter', 10, 2)->nullable()->after('lng');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('absensi', function (Blueprint $table) {
            $table->dropColumn(['lat', 'lng', 'jarak_meter']);
        });
    }
};
