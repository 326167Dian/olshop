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
        Schema::table('barang', function (Blueprint $table) {
            // Default 'resep' (bukan 'bebas') dengan sengaja -- supaya obat lama
            // yang belum ditandai manual oleh admin TIDAK otomatis muncul ke
            // pengunjung yang belum login (storefront ini dipakai sebagai landing
            // page iklan Meta; menampilkan obat resep ke publik berisiko akun iklan
            // kena banned). Admin harus secara eksplisit menandai 'bebas' lewat
            // halaman "Display Jenis Obat" baru di produk itu benar-benar aman
            // ditampilkan ke tamu.
            $table->enum('display', ['resep', 'bebas'])->default('resep')->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('barang', function (Blueprint $table) {
            $table->dropColumn('display');
        });
    }
};
