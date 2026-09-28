<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Potongan keterlambatan (auto-hitung dari Kehadiran periode itu, bisa
     * dioverride manual -- pola sama seperti lembur/komisi). `total` (generated
     * STORED) harus di-drop dulu sebelum kolom baru bisa masuk formulanya, lalu
     * dibuat ulang -- ikuti pola remove_fee_malam_from_gaji_detail_table, JANGAN
     * edit migrasi create_gaji_detail_table yang sudah pernah dijalankan.
     */
    public function up(): void
    {
        Schema::table('gaji_detail', function (Blueprint $table) {
            $table->dropColumn('total');
        });

        Schema::table('gaji_detail', function (Blueprint $table) {
            $table->decimal('potongan_telat', 15, 2)->default(0)->after('pinjaman');
        });

        Schema::table('gaji_detail', function (Blueprint $table) {
            $table->decimal('total', 15, 2)
                ->storedAs('gaji_pokok + transportasi + lembur + komisi - pinjaman - potongan_telat');
        });
    }

    public function down(): void
    {
        Schema::table('gaji_detail', function (Blueprint $table) {
            $table->dropColumn('total');
        });

        Schema::table('gaji_detail', function (Blueprint $table) {
            $table->dropColumn('potongan_telat');
        });

        Schema::table('gaji_detail', function (Blueprint $table) {
            $table->decimal('total', 15, 2)
                ->storedAs('gaji_pokok + transportasi + lembur + komisi - pinjaman');
        });
    }
};
