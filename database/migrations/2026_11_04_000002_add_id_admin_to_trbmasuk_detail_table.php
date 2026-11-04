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
        Schema::table('trbmasuk_detail', function (Blueprint $table) {
            // Admin yang insert/terakhir mengubah baris ini (dipasangkan dengan kolom
            // `waktu` yang sudah ada & auto-update) -- supaya transaksi yang dikerjakan
            // bertahap oleh staf berbeda (mis. mulai hari ini, dilanjutkan besok oleh
            // orang lain) bisa dilacak per baris, bukan cuma per header trbmasuk.
            $table->integer('id_admin')->nullable()->after('tipe_barang');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('trbmasuk_detail', function (Blueprint $table) {
            $table->dropColumn('id_admin');
        });
    }
};
