<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Fitur extends Model
{
    protected $table = 'fitur';
    protected $primaryKey = 'id_fitur';
    public $timestamps = false;

    protected $fillable = [
        'fitur',
        'acc',
    ];

    /**
     * Batas jumlah SKU (barang.stok_barang > 0) untuk tier 'starter'. Melebihi
     * batas ini mengharuskan tier 'premium' aktif, lihat melebihiBatasStarter().
     */
    public const BATAS_STOK_STARTER = 1000;

    /**
     * Nama baris fitur yang menggerbang toko online (produk, keranjang,
     * checkout, dll). acc='Y' (default) -> toko online aktif, tampilan awal
     * domain adalah /home-page. acc='N' -> toko online nonaktif, tampilan awal
     * domain adalah /inventory dan rute e-commerce ditolak, lihat
     * App\Http\Middleware\EnsureEcommerceActive.
     */
    public const NAMA_ECOMMERCE = 'E-Commerce';

    public static function isPremiumAktif(): bool
    {
        return strtoupper((string) static::where('fitur', 'premium')->value('acc')) === 'Y';
    }

    public static function isEcommerceAktif(): bool
    {
        $acc = static::where('fitur', self::NAMA_ECOMMERCE)->value('acc');

        // Baris belum ada (migrasi belum jalan di DB lama) -> anggap aktif,
        // sama seperti perilaku sebelum fitur ini ada.
        if ($acc === null) {
            return true;
        }

        return strtoupper((string) $acc) === 'Y';
    }

    /**
     * true jika jumlah SKU dengan stok_barang > 0 sudah melebihi batas starter
     * DAN tier premium belum diaktifkan admin -- dipakai untuk menolak input
     * barang masuk (PBF/non-PBF) sebelum simpan_detail, lihat
     * InventoryTrbmasukController/InventoryTrbmasukPbfController::detailStore().
     */
    public static function melebihiBatasStarter(): bool
    {
        if (self::isPremiumAktif()) {
            return false;
        }

        return Product::where('stok_barang', '>', 0)->count() > self::BATAS_STOK_STARTER;
    }
}
