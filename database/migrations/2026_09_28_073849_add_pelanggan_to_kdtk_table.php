<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Supaya draft keranjang (stt_kdtk='ON') bisa "sudah tahu" pelanggannya sebelum kasir
     * membuka layar Tambah Penjualan -- dipakai modul Cek Darah supaya hasil cek yang
     * dibebankan ke kasir sudah terpasang ke pelanggan yang benar, tanpa menunggu kasir
     * memilih ulang saat finalisasi (lihat InventoryCekdarahController/resolveKdtk()).
     */
    public function up(): void
    {
        Schema::table('kdtk', function (Blueprint $table) {
            $table->integer('id_pelanggan')->nullable()->after('id_admin');
            $table->string('nm_pelanggan', 100)->nullable()->after('id_pelanggan');
        });
    }

    public function down(): void
    {
        Schema::table('kdtk', function (Blueprint $table) {
            $table->dropColumn(['id_pelanggan', 'nm_pelanggan']);
        });
    }
};
