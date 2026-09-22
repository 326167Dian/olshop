<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Raw SQL, bukan Schema::table(...)->change(), supaya tidak perlu menambah
     * dependency doctrine/dbal cuma untuk mengubah dua kolom jadi nullable.
     */
    public function up(): void
    {
        DB::statement('ALTER TABLE resellers MODIFY nama_bank VARCHAR(255) NULL');
        DB::statement('ALTER TABLE resellers MODIFY no_rekening VARCHAR(50) NULL');
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE resellers MODIFY nama_bank VARCHAR(255) NOT NULL DEFAULT ''");
        DB::statement("ALTER TABLE resellers MODIFY no_rekening VARCHAR(50) NOT NULL DEFAULT ''");
    }
};
