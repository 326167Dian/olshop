<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Admin extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'admin';
    protected $primaryKey = 'id_admin';
    public $timestamps = false;

    /**
     * Kolom flag Y/N di tabel admin yang mengatur akses per modul inventory,
     * dikelompokkan persis seperti sidebar aplikasi legacy (public/apotekberlian).
     * key = nama kolom di tabel admin, value = label yang ditampilkan.
     */
    public const PERMISSION_GROUPS = [
        'Data Master' => [
            'mpengguna' => 'Operator',
            'mheader' => 'Header Struk',
            'mjenisbayar' => 'Jenis Pembayaran',
            'mpelanggan' => 'Pelanggan',
            'msupplier' => 'Supplier',
            'msatuan' => 'Satuan',
            'mjenisobat' => 'Jenis Obat & Rak Obat',
            'mbarang' => 'Item Barang',
            'komisi' => 'Komisi Pegawai',
            'ujian' => 'Ujian',
        ],
        'Inventory' => [
            'mstok' => 'Nilai Stok & Traffic Barang',
            'stok_kritis' => 'Stok Kritis',
            'stokopname' => 'Stok Opname Bulanan',
            'soharian' => 'Stok Opname Harian',
            'kartustok' => 'Kartu Stok',
            'jurnalkas' => 'Jurnal Kas',
        ],
        'Transaksi' => [
            'orders' => 'Pesan Barang',
            'tbm' => 'Barang Masuk non PBF',
            'tbmpbf' => 'Barang Masuk dari PBF',
            'byrkredit' => 'Edit/Retur/Hapus Pembelian',
            'cekdarah' => 'Cek Darah',
            'shiftkerja' => 'Buka/Tutup Kasir',
            'tpk' => 'Penjualan/Kasir',
            'penjualansebelum' => 'Edit/Retur/Hapus Penjualan',
            'catatan' => 'Catatan',
        ],
        'Laporan' => [
            'lpitem' => 'Item Barang',
            'lpbrgmasuk' => 'Barang Masuk',
            'lpkasir' => 'Penjualan',
            'labapenjualan' => 'Laba Penjualan',
            'labajenisobat' => 'Jenis Penjualan',
            'lpsupplier' => 'Data Supplier',
            'lppelanggan' => 'Data Pelanggan',
            'neraca' => 'Neraca Laba Rugi',
        ],
    ];

    protected $fillable = [
        'username',
        'password',
        'nama_lengkap',
        'no_telp',
        'foto',
        'akses_level',
        'unit',
        'blokir',
        'mpengguna',
        'mheader',
        'mjenisbayar',
        'mpelanggan',
        'msupplier',
        'msatuan',
        'mjenisobat',
        'mbarang',
        'tbm',
        'tbmpbf',
        'tpk',
        'lpitem',
        'lpbrgmasuk',
        'lpkasir',
        'lpsupplier',
        'lppelanggan',
        'mstok',
        'stok_kritis',
        'orders',
        'penjualansebelum',
        'labapenjualan',
        'byrkredit',
        'stokopname',
        'soharian',
        'labajenisobat',
        'koreksistok',
        'shiftkerja',
        'neraca',
        'komisi',
        'kartustok',
        'catatan',
        'cekdarah',
        'jurnalkas',
        'ujian',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function articles()
    {
        return $this->hasMany(Article::class, 'user_id', 'id_admin');
    }

    public function products_updated()
    {
        return $this->hasMany(Product::class, 'updated_by', 'id_admin');
    }

    /**
     * Cek apakah admin punya akses ke modul tertentu (kolom flag 'Y'/'N').
     * Modul yang tidak dikenal/tidak ada kolomnya dianggap tidak diizinkan.
     */
    public function hasModuleAccess(string $module): bool
    {
        if (!$this->hasFlagColumn($module)) {
            return false;
        }

        return strtoupper((string) ($this->{$module} ?? 'N')) === 'Y';
    }

    protected function hasFlagColumn(string $module): bool
    {
        foreach (self::PERMISSION_GROUPS as $group) {
            if (array_key_exists($module, $group)) {
                return true;
            }
        }

        return false;
    }

    public function isPemilik(): bool
    {
        return $this->akses_level === 'pemilik';
    }

    public function isBlocked(): bool
    {
        return strtoupper((string) $this->blokir) === 'Y';
    }
}
