<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel 'riwayat_pelanggan' dan 'riwayat_pelanggan_obat' dipakai modul Swamedikasi
     * (public/apotekberlian/masuk/modul/mod_swamedikasi/swamedikasi.php), dibuat lewat
     * migration SQL manual di legacy (public/apotekberlian/database/...), bukan lewat
     * Laravel migration. Migration ini menjamin tabelnya ada di database manapun.
     */
    public function up(): void
    {
        if (!Schema::hasTable('riwayat_pelanggan')) {
            Schema::create('riwayat_pelanggan', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('id_pelanggan');
                $table->unsignedBigInteger('id_admin')->default(0);
                $table->date('tgl');
                $table->text('diagnosa')->nullable();
                $table->text('foto')->default('');
                $table->text('foto2')->default('');
                $table->text('tindakan')->nullable();
                $table->text('followup')->nullable();
                $table->dateTime('tgl_followup')->nullable();
                $table->string('followup_by', 100)->default('');
                $table->timestamp('created_at')->useCurrent();

                $table->index('id_admin', 'idx_riwayat_pelanggan_id_admin');
                $table->index('id_pelanggan', 'idx_riwayat_pelanggan_id_pelanggan');
            });
        }

        if (!Schema::hasTable('riwayat_pelanggan_obat')) {
            Schema::create('riwayat_pelanggan_obat', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('id_riwayat');
                $table->unsignedBigInteger('kd_barang');
                $table->string('nm_barang', 100);
                $table->string('aturan_pakai', 255)->nullable();
                $table->timestamp('created_at')->useCurrent();

                $table->index('id_riwayat', 'idx_rpo_id_riwayat');
                $table->index('kd_barang', 'idx_rpo_kd_barang');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('riwayat_pelanggan_obat');
        Schema::dropIfExists('riwayat_pelanggan');
    }
};
