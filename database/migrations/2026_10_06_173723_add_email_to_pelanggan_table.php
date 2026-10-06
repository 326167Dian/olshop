<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jembatan ke tabel `users` (akun Google e-commerce) -- dicocokkan lewat email,
     * bukan FK/kolom di `users` (users.id tidak diubah sama sekali). Unique supaya
     * satu alamat email cuma pernah terhubung ke SATU baris pelanggan (nullable unique
     * di MySQL mengizinkan banyak NULL, jadi baris lama yang belum pernah ditautkan
     * tidak terganggu). Lihat CustomerCekDarahController untuk alur penautannya.
     */
    public function up(): void
    {
        Schema::table('pelanggan', function (Blueprint $table) {
            $table->string('email', 150)->nullable()->unique()->after('nm_pelanggan');
        });
    }

    public function down(): void
    {
        Schema::table('pelanggan', function (Blueprint $table) {
            $table->dropColumn('email');
        });
    }
};
