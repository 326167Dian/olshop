<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Kolom flag Y/N di tabel admin (izin akses per modul + koreksistok),
     * ditambahkan DEFAULT 'N' agar insert yang tidak menyertakannya (mis.
     * kode lain di masa depan) tidak gagal dengan error "column cannot be
     * null" seperti yang terjadi saat mengerjakan modul Operator/Inventory.
     */
    private const ADMIN_FLAG_COLUMNS = [
        'mpengguna', 'mheader', 'mjenisbayar', 'mpelanggan', 'msupplier', 'msatuan',
        'mjenisobat', 'mbarang', 'komisi', 'ujian',
        'mstok', 'stok_kritis', 'stokopname', 'soharian', 'kartustok', 'jurnalkas',
        'orders', 'tbm', 'tbmpbf', 'byrkredit', 'cekdarah', 'shiftkerja', 'tpk',
        'penjualansebelum', 'catatan',
        'lpitem', 'lpbrgmasuk', 'lpkasir', 'labapenjualan', 'labajenisobat',
        'lpsupplier', 'lppelanggan', 'neraca',
        'koreksistok',
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        foreach (self::ADMIN_FLAG_COLUMNS as $column) {
            if (Schema::hasColumn('admin', $column)) {
                DB::statement("ALTER TABLE admin MODIFY `{$column}` VARCHAR(1) NOT NULL DEFAULT 'N'");
            }
        }

        if (Schema::hasColumn('admin', 'unit')) {
            DB::statement('ALTER TABLE admin MODIFY unit INT(11) NOT NULL DEFAULT 0');
        }

        if (Schema::hasColumn('carabayar', 'urutan')) {
            DB::statement('ALTER TABLE carabayar MODIFY urutan INT(11) NOT NULL DEFAULT 0');
        }

        if (Schema::hasColumn('setheader', 'sebelas')) {
            DB::statement("ALTER TABLE setheader MODIFY sebelas VARCHAR(100) NOT NULL DEFAULT ''");
        }

        if (Schema::hasColumn('pelanggan', 'unit')) {
            DB::statement('ALTER TABLE pelanggan MODIFY unit INT(11) NOT NULL DEFAULT 0');
        }

        if (Schema::hasColumn('pelanggan', 'total_poin')) {
            DB::statement('ALTER TABLE pelanggan MODIFY total_poin INT(11) NOT NULL DEFAULT 0');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach (self::ADMIN_FLAG_COLUMNS as $column) {
            if (Schema::hasColumn('admin', $column)) {
                DB::statement("ALTER TABLE admin MODIFY `{$column}` VARCHAR(1) NOT NULL");
            }
        }

        if (Schema::hasColumn('admin', 'unit')) {
            DB::statement('ALTER TABLE admin MODIFY unit INT(11) NOT NULL');
        }

        if (Schema::hasColumn('carabayar', 'urutan')) {
            DB::statement('ALTER TABLE carabayar MODIFY urutan INT(11) NOT NULL');
        }

        if (Schema::hasColumn('setheader', 'sebelas')) {
            DB::statement('ALTER TABLE setheader MODIFY sebelas VARCHAR(100) NOT NULL');
        }

        if (Schema::hasColumn('pelanggan', 'unit')) {
            DB::statement('ALTER TABLE pelanggan MODIFY unit INT(11) NOT NULL');
        }

        if (Schema::hasColumn('pelanggan', 'total_poin')) {
            DB::statement('ALTER TABLE pelanggan MODIFY total_poin INT(11) NOT NULL');
        }
    }
};
