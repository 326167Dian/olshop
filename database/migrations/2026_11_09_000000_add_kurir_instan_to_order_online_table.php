<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Data pengiriman lewat Kurir Instan (GoSend/GrabExpress via Biteship).
     * kurir_company/kurir_type diisi saat checkout (pelanggan memilih layanan);
     * kolom biteship_* & driver diisi setelah admin memesan kurir dan dari webhook.
     */
    public function up(): void
    {
        Schema::table('order_online', function (Blueprint $table) {
            $table->string('kurir_company', 50)->nullable()->after('layanan_pengiriman');
            $table->string('kurir_type', 50)->nullable()->after('kurir_company');
            $table->string('biteship_order_id', 100)->nullable()->index()->after('kurir_type');
            $table->string('biteship_status', 50)->nullable()->after('biteship_order_id');
            $table->decimal('biteship_price', 12, 2)->nullable()->after('biteship_status');
            $table->string('kurir_tracking_id', 100)->nullable()->after('biteship_price');
            $table->string('kurir_waybill_id', 100)->nullable()->after('kurir_tracking_id');
            $table->string('kurir_driver_name', 100)->nullable()->after('kurir_waybill_id');
            $table->string('kurir_driver_phone', 30)->nullable()->after('kurir_driver_name');
            $table->string('kurir_driver_plate', 30)->nullable()->after('kurir_driver_phone');
            $table->string('kurir_link', 500)->nullable()->after('kurir_driver_plate');
        });
    }

    public function down(): void
    {
        Schema::table('order_online', function (Blueprint $table) {
            $table->dropIndex(['biteship_order_id']);
            $table->dropColumn([
                'kurir_company',
                'kurir_type',
                'biteship_order_id',
                'biteship_status',
                'biteship_price',
                'kurir_tracking_id',
                'kurir_waybill_id',
                'kurir_driver_name',
                'kurir_driver_phone',
                'kurir_driver_plate',
                'kurir_link',
            ]);
        });
    }
};
