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
        Schema::table('company_settings', function (Blueprint $table) {
            // Persentase komisi reseller dari total transaksi pelanggan yang mereka
            // rekrut. Bisa diubah admin kapan saja, jadi disimpan sebagai setting,
            // bukan konstanta di kode.
            $table->decimal('komisi_reseller', 5, 2)->default(5.00)->after('catatan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('company_settings', function (Blueprint $table) {
            $table->dropColumn('komisi_reseller');
        });
    }
};
