<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Kolom 'image' dan 'catatan' dimaksudkan opsional (foto & catatan penyerahan pesanan).
     * Di sebagian environment kolom ini sudah dibuat manual sebagai NOT NULL tanpa default
     * (perlu di-MODIFY jadi nullable), di environment lain kolomnya belum ada sama sekali
     * (perlu dibuat baru sebagai nullable) — migration ini menangani kedua kondisi.
     */
    public function up(): void
    {
        if (Schema::hasColumn('order_online', 'image')) {
            DB::statement('ALTER TABLE order_online MODIFY image TEXT NULL');
        } else {
            Schema::table('order_online', function (Blueprint $table) {
                $table->text('image')->nullable()->after('waktu_approval');
            });
        }

        if (Schema::hasColumn('order_online', 'catatan')) {
            DB::statement('ALTER TABLE order_online MODIFY catatan VARCHAR(255) NULL');
        } else {
            Schema::table('order_online', function (Blueprint $table) {
                $table->string('catatan', 255)->nullable()->after('image');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('order_online', 'catatan')) {
            Schema::table('order_online', function (Blueprint $table) {
                $table->dropColumn('catatan');
            });
        }

        if (Schema::hasColumn('order_online', 'image')) {
            Schema::table('order_online', function (Blueprint $table) {
                $table->dropColumn('image');
            });
        }
    }
};
