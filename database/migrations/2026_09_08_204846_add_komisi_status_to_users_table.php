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
        Schema::table('users', function (Blueprint $table) {
            // Status pembayaran komisi ke petugas (users.referal_admin_id) yang
            // merekrut customer ini. 'lunas' hanya boleh diubah oleh pemilik (dicek
            // di CustomerController::updateKomisi()), waktu_komisi_lunas dicatat
            // otomatis saat status berpindah ke 'lunas', dikosongkan lagi kalau
            // dikembalikan ke 'belum'.
            $table->enum('komisi_status', ['belum', 'lunas'])->default('belum')->after('referal_admin_id');
            $table->timestamp('waktu_komisi_lunas')->nullable()->after('komisi_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['komisi_status', 'waktu_komisi_lunas']);
        });
    }
};
