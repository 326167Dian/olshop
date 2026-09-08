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
        Schema::table('users', function (Blueprint $table) {
            // Menampung admin.id_admin (petugas) yang mereferensikan customer ini saat
            // daftar/lengkapi akun -- tidak dibuat foreign key literal karena `admin` dan
            // `users` adalah dua domain tabel berbeda (staf vs pelanggan), sama seperti
            // relasi lintas-domain lain di app ini (mis. Product.category_id).
            // int(11), bukan unsignedBigInteger, supaya persis sama tipenya dengan
            // admin.id_admin (int(11)) yang dirujuknya.
            $table->integer('referal_admin_id')->nullable()->after('no_tlp');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('referal_admin_id');
        });
    }
};
