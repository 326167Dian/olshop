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
        Schema::table('order_online', function (Blueprint $table) {
            $table->unsignedBigInteger('lokasi_antar_id')->nullable()->after('tipe_layanan');
            $table->double('biaya_ongkir')->default(0)->after('lokasi_antar_id');

            $table->foreign('lokasi_antar_id')->references('id')->on('lokasi_antar')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_online', function (Blueprint $table) {
            $table->dropForeign(['lokasi_antar_id']);
            $table->dropColumn(['lokasi_antar_id', 'biaya_ongkir']);
        });
    }
};
