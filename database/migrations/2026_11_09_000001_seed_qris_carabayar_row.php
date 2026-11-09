<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Tabel `carabayar` cuma pernah berisi Tunai/Transfer/Tempo (data produksi lama)
     * -- tidak pernah ada baris "QRIS", padahal Kasir POS (lihat create.blade.php,
     * simpanTransaksi()) mendeteksi QRIS Otomatis dengan mencocokkan nm_carabayar
     * === 'QRIS', dan checkout e-commerce (OrderController) butuh id_carabayar yang
     * valid untuk pesanan QRIS Dinamis (GDC Pay). Tanpa baris ini, QRIS Otomatis di
     * Kasir tidak pernah muncul di dropdown dan laporan trkasir pesanan QRIS
     * tampil dengan Cara Bayar kosong.
     */
    public function up(): void
    {
        if (DB::table('carabayar')->where('nm_carabayar', 'QRIS')->doesntExist()) {
            DB::table('carabayar')->insert(['nm_carabayar' => 'QRIS', 'urutan' => 4]);
        }
    }

    public function down(): void
    {
        DB::table('carabayar')->where('nm_carabayar', 'QRIS')->delete();
    }
};
