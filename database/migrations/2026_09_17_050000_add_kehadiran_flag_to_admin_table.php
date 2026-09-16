<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Flag akses modul "Kehadiran Pegawai" (shift/absensi/lembur/cuti) --
     * mengikuti pola flag Y/N lain di tabel admin (mis. komisi, shiftkerja).
     */
    public function up(): void
    {
        Schema::table('admin', function (Blueprint $table) {
            $table->string('kehadiran', 1)->default('N')->after('rekening_bank');
        });
    }

    public function down(): void
    {
        Schema::table('admin', function (Blueprint $table) {
            $table->dropColumn('kehadiran');
        });
    }
};
