<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel 'pto' di legacy dibuat secara ad-hoc oleh
     * public/apotekberlian/masuk/modul/mod_pemantauan_terapi_obat/simpan_pto.php
     * (CREATE TABLE IF NOT EXISTS saat submit pertama), bukan lewat migration.
     * Migration ini menjamin tabelnya ada di database manapun (termasuk hosting
     * yang belum pernah menjalankan form PTO legacy sama sekali).
     */
    public function up(): void
    {
        if (Schema::hasTable('pto')) {
            return;
        }

        Schema::create('pto', function (Blueprint $table) {
            $table->id('id_pto');
            $table->unsignedBigInteger('id_pelanggan');
            $table->string('nm_pelanggan', 120)->nullable();
            $table->string('jenis_kelamin', 30)->nullable();
            $table->string('umur', 30)->nullable();
            $table->text('alamat_pelanggan')->nullable();
            $table->string('tlp_pelanggan', 30)->nullable();
            $table->date('tanggal_1')->nullable();
            $table->text('catatan_1')->nullable();
            $table->text('obat_1')->nullable();
            $table->text('masalah_1')->nullable();
            $table->text('tindak_1')->nullable();
            $table->date('tanggal_2')->nullable();
            $table->text('catatan_2')->nullable();
            $table->text('obat_2')->nullable();
            $table->text('masalah_2')->nullable();
            $table->text('tindak_2')->nullable();
            $table->string('tempat_ttd', 120)->nullable();
            $table->date('tanggal_ttd')->nullable();
            $table->string('created_by', 120)->nullable();
            $table->dateTime('created_at')->useCurrent();

            $table->index('id_pelanggan', 'idx_pto_pelanggan');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pto');
    }
};
