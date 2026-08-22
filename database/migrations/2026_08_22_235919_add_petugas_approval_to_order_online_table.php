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
        Schema::table('order_online', function (Blueprint $table) {
            if (!Schema::hasColumn('order_online', 'petugas_approval')) {
                $table->string('petugas_approval')->nullable()->after('bukti_pembayaran');
            }
            if (!Schema::hasColumn('order_online', 'waktu_approval')) {
                $table->timestamp('waktu_approval')->nullable()->after('petugas_approval');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_online', function (Blueprint $table) {
            if (Schema::hasColumn('order_online', 'waktu_approval')) {
                $table->dropColumn('waktu_approval');
            }
            if (Schema::hasColumn('order_online', 'petugas_approval')) {
                $table->dropColumn('petugas_approval');
            }
        });
    }
};
