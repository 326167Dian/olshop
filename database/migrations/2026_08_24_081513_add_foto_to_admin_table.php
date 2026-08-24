<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Tabel 'admin' dipakai bersama aplikasi POS Smart Inventory — hanya menambah
     * kolom nullable di akhir tabel, tidak mengubah/menghapus kolom yang sudah ada.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('admin', 'foto')) {
            Schema::table('admin', function (Blueprint $table) {
                $table->string('foto')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('admin', 'foto')) {
            Schema::table('admin', function (Blueprint $table) {
                $table->dropColumn('foto');
            });
        }
    }
};
