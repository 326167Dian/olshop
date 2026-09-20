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
        Schema::table('trkasir_detail', function (Blueprint $table) {
            // int signed (bukan unsignedBigInteger) supaya konsisten dengan kolom id_*
            // lain di tabel legacy ini (idadmin, id_barang, dst juga int(11) signed).
            $table->integer('id_reseller')->nullable()->after('idadmin');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('trkasir_detail', function (Blueprint $table) {
            $table->dropColumn('id_reseller');
        });
    }
};
