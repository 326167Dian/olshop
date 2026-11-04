<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Migration create_fitur_table (2026_10_15) tidak pernah diikuti seed baris
     * 'starter'/'premium'-nya, jadi tabel `fitur` kosong di hosting maupun lokal --
     * halaman Pengaturan Fitur tampil tanpa baris sama sekali sehingga tier premium
     * tidak bisa diaktifkan lewat UI. Migration ini mengisi kedua baris yang
     * Fitur::isPremiumAktif()/blade-nya harapkan, starter aktif secara default.
     */
    public function up(): void
    {
        if (DB::table('fitur')->count() === 0) {
            DB::table('fitur')->insert([
                ['fitur' => 'starter', 'acc' => 'Y'],
                ['fitur' => 'premium', 'acc' => 'N'],
            ]);
        }
    }

    public function down(): void
    {
        DB::table('fitur')->whereIn('fitur', ['starter', 'premium'])->delete();
    }
};
