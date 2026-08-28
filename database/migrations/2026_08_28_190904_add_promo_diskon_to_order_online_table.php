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
            if (!Schema::hasColumn('order_online', 'promo_id')) {
                $table->unsignedBigInteger('promo_id')->nullable()->after('total_harga');
            }
            if (!Schema::hasColumn('order_online', 'nama_promo')) {
                $table->string('nama_promo')->nullable()->after('promo_id');
            }
            if (!Schema::hasColumn('order_online', 'nilai_diskon_promo')) {
                $table->decimal('nilai_diskon_promo', 5, 2)->nullable()->after('nama_promo');
            }
            if (!Schema::hasColumn('order_online', 'total_diskon')) {
                $table->double('total_diskon')->nullable()->after('nilai_diskon_promo');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_online', function (Blueprint $table) {
            foreach (['promo_id', 'nama_promo', 'nilai_diskon_promo', 'total_diskon'] as $column) {
                if (Schema::hasColumn('order_online', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
