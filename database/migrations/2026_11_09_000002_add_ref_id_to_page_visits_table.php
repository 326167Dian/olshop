<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * `page_visits.page` cuma menyimpan nama route (mis. 'produk.kategori'), sama
     * untuk SEMUA kategori yang dikunjungi -- jadi laporan kunjungan tidak pernah
     * bisa dipecah per nama kategori/produk/artikel. Kolom ini menyimpan parameter
     * pertama route-nya (id kategori, id produk, atau slug artikel/video), diisi
     * mulai sekarang oleh TrackPageVisit -- kunjungan SEBELUM migration ini tetap
     * tidak bisa dipecah karena datanya memang tidak pernah direkam.
     */
    public function up(): void
    {
        Schema::table('page_visits', function (Blueprint $table) {
            $table->string('ref_id')->nullable()->after('page');
        });
    }

    public function down(): void
    {
        Schema::table('page_visits', function (Blueprint $table) {
            $table->dropColumn('ref_id');
        });
    }
};
