<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('order_online', function (Blueprint $table) {
            if (!Schema::hasColumn('order_online', 'bukti_pembayaran')) {
                $table->string('bukti_pembayaran')->nullable()->after('midtrans_order_id');
            }
        });

        // tipe_pembayaran was varchar(10), too short for values like 'Qris'/'Bank Transfer'.
        if (Schema::hasColumn('order_online', 'tipe_pembayaran')) {
            DB::statement('ALTER TABLE order_online MODIFY tipe_pembayaran VARCHAR(30) NULL');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_online', function (Blueprint $table) {
            if (Schema::hasColumn('order_online', 'bukti_pembayaran')) {
                $table->dropColumn('bukti_pembayaran');
            }
        });

        if (Schema::hasColumn('order_online', 'tipe_pembayaran')) {
            DB::statement('ALTER TABLE order_online MODIFY tipe_pembayaran VARCHAR(10) NULL');
        }
    }
};
