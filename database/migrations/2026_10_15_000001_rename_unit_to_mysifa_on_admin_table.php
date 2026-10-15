<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Kolom 'unit' (int, tidak terpakai di manapun selain Admin::$fillable) diubah
     * jadi 'mysifa' (varchar(1), flag Y/N) -- flag vendor khusus untuk mengunci
     * halaman /inventory/fitur, lihat Admin::isMySifa() & InventoryFiturController.
     * SENGAJA tidak lewat hasModuleAccess()/PERMISSION_GROUPS supaya pemilik apotek
     * tidak bisa membuka kuncinya sendiri lewat form centang izin Admin.
     *
     * Dijaga hasColumn() karena perubahan ini sudah dilakukan manual lewat
     * phpMyAdmin di DB produksi sebelum migration ini dibuat (lihat
     * add_foto_to_admin_table untuk pola yang sama).
     */
    public function up(): void
    {
        if (Schema::hasColumn('admin', 'unit') && !Schema::hasColumn('admin', 'mysifa')) {
            DB::statement("ALTER TABLE admin CHANGE unit mysifa VARCHAR(1) NOT NULL DEFAULT 'N'");
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('admin', 'mysifa') && !Schema::hasColumn('admin', 'unit')) {
            DB::statement("ALTER TABLE admin CHANGE mysifa unit INT(11) NOT NULL DEFAULT 0");
        }
    }
};
