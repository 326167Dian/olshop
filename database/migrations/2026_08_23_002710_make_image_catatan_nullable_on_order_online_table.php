<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Kolom 'image' dan 'catatan' ditambahkan manual sebagai NOT NULL tanpa default,
     * padahal keduanya dimaksudkan opsional (foto & catatan penyerahan pesanan) —
     * ini membuat pembuatan order baru (tanpa nilai kolom ini) selalu gagal.
     */
    public function up(): void
    {
        DB::statement('ALTER TABLE order_online MODIFY image TEXT NULL');
        DB::statement('ALTER TABLE order_online MODIFY catatan VARCHAR(255) NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE order_online MODIFY image TEXT NOT NULL');
        DB::statement('ALTER TABLE order_online MODIFY catatan VARCHAR(255) NOT NULL');
    }
};
