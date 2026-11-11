<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Fallback untuk 2026_10_15_000001_rename_unit_to_mysifa_on_admin_table: migration itu
     * cuma jalan kalau admin.unit ada (ganti nama kolom). Di site yang admin.unit-nya sudah
     * tidak pernah ada sejak awal (mis. azzamonline), migration tsb no-op, jadi admin.mysifa
     * tidak pernah tercipta walau sudah tercatat "Ran" -- bikin Admin::bukanAkunMysifa() gagal
     * dengan SQLSTATE[42S22] Column not found.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('admin', 'mysifa')) {
            DB::statement("ALTER TABLE admin ADD COLUMN mysifa VARCHAR(1) NOT NULL DEFAULT 'N'");
        }
    }

    public function down(): void
    {
        // Sengaja no-op: kolom ini juga dipakai/di-drop oleh migration rename_unit_to_mysifa,
        // drop di sini berisiko bentrok kalau keduanya di-rollback berurutan.
    }
};
