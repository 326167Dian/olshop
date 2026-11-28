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

    /**
     * Nama baris "kunci masa transisi" -- dipakai vendor (akun admin.mysifa='Y')
     * untuk mengunci SELURUH modul inventory kecuali Buka/Tutup Kasir &
     * Penjualan/Kasir, saat pelanggan belum memberi kejelasan perpanjangan
     * lisensi tahun berikutnya. Tujuannya supaya operasional harian (kasir)
     * tetap bisa jalan tanpa terhenti, TAPI data lain (laporan, data master, dll)
     * tidak lagi bisa diakses/diunduh selama masa transisi ini, sebagai
     * perlindungan data sebelum perjanjian benar-benar berakhir. Baris ini HANYA
     * bisa ditoggle lewat "Pengaturan Fitur" (khusus pemilik+mysifa, lihat
     * InventoryFiturController) -- pemilik apotek sendiri TIDAK bisa
     * menonaktifkannya sendiri, sama seperti tier starter/premium.
     */
    public const NAMA_TRANSISI = 'transisi';

    public static function isPremiumAktif(): bool
    {
        return strtoupper((string) static::where('fitur', 'premium')->value('acc')) === 'Y';
    }

    public static function isTransisiAktif(): bool
    {
        return strtoupper((string) static::where('fitur', self::NAMA_TRANSISI)->value('acc')) === 'Y';
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
