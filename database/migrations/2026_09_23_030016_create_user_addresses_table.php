<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Buku alamat "Alamat Lain" pelanggan -- dikelola inline di halaman checkout
     * shipping (dropdown alamat tersimpan + "Tambah Alamat Baru"), bukan halaman
     * akun terpisah. Order menyimpan snapshot lat/lng/teks/label-nya sendiri di
     * order_online, jadi baris di sini boleh diedit/dihapus tanpa mengubah pesanan lama.
     */
    public function up(): void
    {
        Schema::create('user_addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('label', 100);
            $table->text('alamat');
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_addresses');
    }
};
