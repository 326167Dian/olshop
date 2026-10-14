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

    public static function isPremiumAktif(): bool
    {
        return strtoupper((string) static::where('fitur', 'premium')->value('acc')) === 'Y';
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
