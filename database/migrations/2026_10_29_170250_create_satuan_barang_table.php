<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    /**
     * Satuan bertingkat per item (mis. Tablet -> Strip -> Box), menggantikan skema lama
     * `barang.sat_barang`/`sat_grosir`/`konversi` yang cuma 2 tingkat. Tidak menghapus
     * kolom lama -- item yang belum dilengkapi di sini tetap jalan pakai skema lama.
     *
     * `rasio_ke_bawah` = berapa satuan di tingkat SATU DI BAWAHNYA ada dalam 1 satuan
     * ini (urutan=1/satuan dasar selalu 1, tidak dipakai). Konversi ke satuan dasar
     * dihitung berantai (kumulatif), bukan disimpan -- lihat SatuanBarang::kumulatifMap().
     * Baris transaksi (trkasir_detail dst, belum dibangun di tahap ini) WAJIB menyalin
     * rasio & harga saat itu juga, bukan merujuk live ke tabel ini -- supaya histori
     * transaksi tidak berubah kalau rasio/harga di sini diedit belakangan.
     */
    public function up(): void
    {
        Schema::create('satuan_barang', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('kd_barang');
            $table->unsignedTinyInteger('urutan');
            $table->integer('id_satuan');
            $table->decimal('rasio_ke_bawah', 10, 2)->default(1);
            $table->decimal('harga_jual', 15, 2)->default(0);
            $table->decimal('harga_beli', 15, 2)->default(0);
            $table->enum('status', ['aktif', 'nonaktif'])->default('aktif');
            $table->timestamps();

            $table->unique(['kd_barang', 'urutan']);
            $table->foreign('kd_barang')->references('kd_barang')->on('barang')->cascadeOnDelete();
            $table->foreign('id_satuan')->references('id_satuan')->on('satuan')->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('satuan_barang');
    }
};
