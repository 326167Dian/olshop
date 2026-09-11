<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('setheader', function (Blueprint $table) {
            // Sama seperti logo/tandatangan: text NOT NULL DEFAULT '' (bukan nullable),
            // mengikuti tipe kedua kolom file yang sudah ada di tabel ini.
            $table->text('capapotek')->default('')->after('tandatangan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('setheader', function (Blueprint $table) {
            $table->dropColumn('capapotek');
        });
    }
};
