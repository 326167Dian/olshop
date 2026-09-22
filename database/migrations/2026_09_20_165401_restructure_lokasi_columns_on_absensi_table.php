<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Check-out sekarang juga divalidasi radius, jadi butuh kolom lokasi
     * terpisah dari check-in (satu baris absensi punya dua event: masuk &
     * pulang). Aman drop+add karena kolom lat/lng/jarak_meter sebelumnya belum
     * pernah terisi data (fitur baru dipasang, belum ada check-in nyata).
     */
    public function up(): void
    {
        Schema::table('absensi', function (Blueprint $table) {
            $table->dropColumn(['lat', 'lng', 'jarak_meter']);
        });

        Schema::table('absensi', function (Blueprint $table) {
            $table->decimal('lat_masuk', 10, 7)->nullable()->after('jam_pulang');
            $table->decimal('lng_masuk', 10, 7)->nullable()->after('lat_masuk');
            $table->decimal('jarak_masuk_meter', 10, 2)->nullable()->after('lng_masuk');
            $table->decimal('lat_pulang', 10, 7)->nullable()->after('jarak_masuk_meter');
            $table->decimal('lng_pulang', 10, 7)->nullable()->after('lat_pulang');
            $table->decimal('jarak_pulang_meter', 10, 2)->nullable()->after('lng_pulang');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('absensi', function (Blueprint $table) {
            $table->dropColumn(['lat_masuk', 'lng_masuk', 'jarak_masuk_meter', 'lat_pulang', 'lng_pulang', 'jarak_pulang_meter']);
        });

        Schema::table('absensi', function (Blueprint $table) {
            $table->decimal('lat', 10, 7)->nullable()->after('jam_pulang');
            $table->decimal('lng', 10, 7)->nullable()->after('lat');
            $table->decimal('jarak_meter', 10, 2)->nullable()->after('lng');
        });
    }
};
