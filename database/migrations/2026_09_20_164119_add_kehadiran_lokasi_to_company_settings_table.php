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
        Schema::table('company_settings', function (Blueprint $table) {
            // Titik koordinat apotek + radius toleransi (meter) untuk validasi
            // check-in/check-out kehadiran pegawai berdasarkan lokasi HP mereka.
            // Nullable -- kalau belum diisi pemilik, validasi radius dilewati saja
            // (tidak mengunci semua orang sebelum fitur ini dikonfigurasi).
            $table->decimal('kehadiran_lat', 10, 7)->nullable()->after('peta_lokasi');
            $table->decimal('kehadiran_lng', 10, 7)->nullable()->after('kehadiran_lat');
            $table->unsignedInteger('kehadiran_radius')->default(100)->after('kehadiran_lng');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('company_settings', function (Blueprint $table) {
            $table->dropColumn(['kehadiran_lat', 'kehadiran_lng', 'kehadiran_radius']);
        });
    }
};
