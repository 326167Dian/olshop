<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * "Glukosa" lama (kolom gula) jadi "Glukosa Puasa" -- gula_2pp menampung hasil
     * cek glukosa 2 jam setelah makan (2PP), sesuai tabel referensi yang sudah ada
     * di halaman cetak (lihat cekdarah/print.blade.php). Nullable karena baris lama
     * belum punya nilai ini.
     */
    public function up(): void
    {
        Schema::table('cekdarah', function (Blueprint $table) {
            $table->string('gula_2pp', 50)->nullable()->after('gula');
        });
    }

    public function down(): void
    {
        Schema::table('cekdarah', function (Blueprint $table) {
            $table->dropColumn('gula_2pp');
        });
    }
};
