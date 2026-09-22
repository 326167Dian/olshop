<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Snapshot alamat pengantaran (bukan referensi ke user_addresses) supaya edit/hapus
     * alamat tersimpan pelanggan tidak mengubah data pesanan lama. jarak_km & estimasi_antar
     * dipakai untuk menampilkan info pengantaran radius-based di invoice/riwayat.
     */
    public function up(): void
    {
        Schema::table('order_online', function (Blueprint $table) {
            $table->string('tipe_alamat', 10)->nullable()->after('alamat');
            $table->string('alamat_label', 100)->nullable()->after('tipe_alamat');
            $table->decimal('alamat_lat', 10, 7)->nullable()->after('alamat_label');
            $table->decimal('alamat_lng', 10, 7)->nullable()->after('alamat_lat');
            $table->decimal('jarak_km', 6, 2)->nullable()->after('lokasi_antar_id');
            $table->dateTime('estimasi_antar')->nullable()->after('layanan_pengiriman');
        });
    }

    public function down(): void
    {
        Schema::table('order_online', function (Blueprint $table) {
            $table->dropColumn([
                'tipe_alamat',
                'alamat_label',
                'alamat_lat',
                'alamat_lng',
                'jarak_km',
                'estimasi_antar',
            ]);
        });
    }
};
