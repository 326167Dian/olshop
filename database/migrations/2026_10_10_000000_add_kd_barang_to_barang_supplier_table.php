<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('barang_supplier', function (Blueprint $table) {
            $table->string('kd_barang', 50)->nullable()->after('id_barang');
        });

        DB::table('barang_supplier as bs')
            ->join('barang as b', 'b.id_barang', '=', 'bs.id_barang')
            ->update(['bs.kd_barang' => DB::raw('b.kd_barang')]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('barang_supplier', function (Blueprint $table) {
            $table->dropColumn('kd_barang');
        });
    }
};
