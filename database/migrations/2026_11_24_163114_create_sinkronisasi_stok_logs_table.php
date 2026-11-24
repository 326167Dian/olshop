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
        // Satu baris per BARANG yang nilainya benar-benar berubah pada satu eksekusi
        // tombol "SINKRONISASI" (InventoryTrkasirController::sinkronisasiStok()) --
        // dibuat atas permintaan user 2026-11-24 setelah insiden stok Panadol 500 mg
        // 10 Kaplet berubah tanpa jejak sama sekali sebelum sinkronisasi dijalankan
        // (lihat catatan di method tsb). kode_sinkron mengelompokkan semua baris dari
        // SATU klik yang sama, supaya "3 barang diperbarui" di pesan sukses bisa
        // ditelusuri detailnya barang apa saja.
        Schema::create('sinkronisasi_stok_logs', function (Blueprint $table) {
            $table->id();
            $table->string('kode_sinkron', 30)->index();
            $table->integer('id_barang');
            $table->unsignedBigInteger('kd_barang');
            $table->string('nm_barang', 100);
            $table->double('stok_sebelum');
            $table->double('stok_sesudah');
            $table->double('selisih');
            $table->integer('id_admin');
            $table->timestamp('waktu');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sinkronisasi_stok_logs');
    }
};
