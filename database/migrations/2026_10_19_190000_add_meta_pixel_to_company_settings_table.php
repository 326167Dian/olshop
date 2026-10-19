<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Meta Pixel per apotek -- diisi lewat menu Setting > Input Pixel, supaya
 * aplikasi yang sama bisa dipakai apotek lain tanpa menyentuh kode/.env.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('company_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('company_settings', 'meta_pixel_id')) {
                $table->string('meta_pixel_id', 30)->nullable();
            }
            if (!Schema::hasColumn('company_settings', 'meta_pixel_aktif')) {
                $table->boolean('meta_pixel_aktif')->default(true);
            }
        });
    }

    public function down(): void
    {
        Schema::table('company_settings', function (Blueprint $table) {
            $table->dropColumn(['meta_pixel_id', 'meta_pixel_aktif']);
        });
    }
};
