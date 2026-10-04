<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Dipakai untuk sinkronisasi idempoten entri jurnal otomatis yang terikat ke satu
     * transaksi sumber (mis. `trbmasuk.kd_trbmasuk`) -- supaya sinkronisasi bisa mencari
     * "apakah transaksi ini sudah pernah punya entri jurnal" lalu UPDATE/DELETE entri yang
     * sama, bukan selalu INSERT baris baru. Nullable karena entri manual (lewat form
     * Jurnal Kas biasa) tidak terikat ke transaksi sumber mana pun.
     */
    public function up(): void
    {
        Schema::table('jurnal', function (Blueprint $table) {
            $table->string('kd_referensi', 100)->nullable()->after('idjenis');
        });
    }

    public function down(): void
    {
        Schema::table('jurnal', function (Blueprint $table) {
            $table->dropColumn('kd_referensi');
        });
    }
};
