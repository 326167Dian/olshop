<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Pada database hosting, kolom `id_shift` di tabel `waktukerja` ternyata tidak
     * AUTO_INCREMENT (beda dengan skema lokal), sehingga Buka Kasir gagal dengan
     * "Field 'id_shift' doesn't have a default value". Migration ini menyamakan skema
     * hosting dengan skema lokal yang sudah benar.
     */
    public function up(): void
    {
        DB::statement('ALTER TABLE `waktukerja` MODIFY `id_shift` INT(11) NOT NULL AUTO_INCREMENT');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE `waktukerja` MODIFY `id_shift` INT(11) NOT NULL');
    }
};
