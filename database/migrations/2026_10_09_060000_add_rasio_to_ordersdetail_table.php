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
        Schema::table('ordersdetail', function (Blueprint $table) {
            // Snapshot rasio stok terhadap kebutuhan (lihat InventoryOrdersController::hitungRasio()):
            // (qty_dtrbmasuk + barang.stok_barang) / Q30 * 100%, dihitung & disimpan SAAT baris
            // ini dibuat/diubah -- sengaja disimpan (bukan dihitung ulang tiap tampil) supaya
            // surat pesanan tetap menunjukkan data aktual saat dibuat, bukan angka yang berubah
            // mengikuti stok/Q30 terkini. Null kalau Q30=0 (tidak ada penjualan 30 hari terakhir,
            // rasio tidak bisa dihitung).
            $table->decimal('rasio', 10, 2)->nullable()->after('qty_dtrbmasuk');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ordersdetail', function (Blueprint $table) {
            $table->dropColumn('rasio');
        });
    }
};
