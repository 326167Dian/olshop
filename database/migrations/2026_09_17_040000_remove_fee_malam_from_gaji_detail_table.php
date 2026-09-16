<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Fee Malam dihapus dari perhitungan Slip Gaji -- sistem ini bukan untuk
     * apotek 24 jam. `total` (kolom generated STORED) harus di-drop dulu
     * sebelum fee_malam bisa di-drop (formula-nya masih mereferensikan kolom
     * itu), lalu dibuat ulang tanpa suku fee_malam.
     */
    public function up(): void
    {
        Schema::table('gaji_detail', function (Blueprint $table) {
            $table->dropColumn('total');
        });

        Schema::table('gaji_detail', function (Blueprint $table) {
            $table->dropColumn('fee_malam');
        });

        Schema::table('gaji_detail', function (Blueprint $table) {
            $table->decimal('total', 15, 2)
                ->storedAs('gaji_pokok + transportasi + lembur + komisi - pinjaman');
        });
    }

    public function down(): void
    {
        Schema::table('gaji_detail', function (Blueprint $table) {
            $table->dropColumn('total');
        });

        Schema::table('gaji_detail', function (Blueprint $table) {
            $table->decimal('fee_malam', 15, 2)->default(0)->after('komisi');
        });

        Schema::table('gaji_detail', function (Blueprint $table) {
            $table->decimal('total', 15, 2)
                ->storedAs('gaji_pokok + transportasi + lembur + komisi + fee_malam - pinjaman');
        });
    }
};
