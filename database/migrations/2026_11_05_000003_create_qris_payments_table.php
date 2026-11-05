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
        // Dipakai BERSAMA oleh checkout marketplace ('source'=marketplace, terhubung
        // ke order_online lewat order_id) dan Kasir/POS ('source'=kasir, kd_trkasir
        // baru terisi setelah Trkasir difinalisasi -- saat QR dibuat transaksi Kasir
        // belum ada). QR dibuat/dicek lewat hub mysifacenter.my.id (lihat HubQrisService),
        // bukan langsung ke GDC.
        Schema::create('qris_payments', function (Blueprint $table) {
            $table->id();
            $table->enum('source', ['marketplace', 'kasir']);
            $table->uuid('reference_no')->unique();
            $table->string('hub_reference_no')->nullable();
            $table->foreignId('order_id')->nullable()->constrained('order_online')->nullOnDelete();
            $table->string('kd_trkasir', 100)->nullable();
            $table->unsignedBigInteger('amount');
            $table->text('qr_content')->nullable();
            $table->string('status', 20)->default('pending');
            $table->timestamp('paid_at')->nullable();
            $table->dateTime('expires_at');
            $table->timestamp('last_checked_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('qris_payments');
    }
};
