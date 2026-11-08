<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Baris fitur ketiga (id_fitur=3) yang menggerbang toko online: acc='Y'
     * (default, supaya toko yang sudah berjalan tidak tiba-tiba mati) membuat
     * tampilan awal domain tetap /home-page dan rute e-commerce tetap bisa
     * diakses; acc='N' membuat tampilan awal jadi /inventory dan menolak semua
     * rute e-commerce, lihat Fitur::isEcommerceAktif() &
     * App\Http\Middleware\EnsureEcommerceActive.
     */
    public function up(): void
    {
        if (DB::table('fitur')->where('fitur', 'E-Commerce')->doesntExist()) {
            DB::table('fitur')->insert(['fitur' => 'E-Commerce', 'acc' => 'Y']);
        }
    }

    public function down(): void
    {
        DB::table('fitur')->where('fitur', 'E-Commerce')->delete();
    }
};
