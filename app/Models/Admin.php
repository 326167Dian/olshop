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
            // 'kartustok' => 'Kartu Stok', -- dikomentari 2026-09-06 atas permintaan
            // user: modul ini tidak pernah benar-benar dipakai. Dihapus dari sini
            // membuatnya otomatis hilang dari sidebar & form centang izin Admin
            // (keduanya digerbang lewat PERMISSION_GROUPS), tanpa perlu menyentuh
            // kolom `admin.kartustok` di database.
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
            'kehadiran' => 'Kehadiran Pegawai',
        ],
        'Laporan' => [
            'lpitem' => 'Item Barang',
            'lpbrgmasuk' => 'Barang Masuk',
            'lpkasir' => 'Penjualan',
            'labapenjualan' => 'Laba Penjualan',
            'labajenisobat' => 'Detail Jenis Penjualan',
            // 'lpsupplier' => 'Data Supplier', -- dikomentari 2026-09-06 atas
            // permintaan user: sudah ada laporan yang sama di Data Master
            // (menu Supplier), jadi duplikat di Laporan dihapus permanen.
            // 'lppelanggan' => 'Data Pelanggan', -- sama alasannya, sudah ada di
            // Data Master (menu Pelanggan).
            'neraca' => 'Neraca Laba Rugi',
            'lapstokopname' => 'Stok Opname',
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
        'nama_bank',
        'rekening_bank',
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
        'lapstokopname',
        'komisi',
        'kartustok',
        'catatan',
        'cekdarah',
        'jurnalkas',
        'ujian',
        'kehadiran',
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
     * Data Gaji Karyawan (modul Gaji, satu baris per admin). Lihat
     * [[App\Models\Gaji]].
     */
    public function gaji()
    {
        return $this->hasOne(Gaji::class, 'id_admin', 'id_admin');
    }

    /**
     * Modul Kehadiran Pegawai -- lihat [[App\Models\JadwalShift]],
     * [[App\Models\Absensi]], [[App\Models\Lembur]], [[App\Models\Cuti]].
     */
    public function jadwalShift()
    {
        return $this->hasMany(JadwalShift::class, 'id_admin', 'id_admin');
    }

    public function absensi()
    {
        return $this->hasMany(Absensi::class, 'id_admin', 'id_admin');
    }

    public function lembur()
    {
        return $this->hasMany(Lembur::class, 'id_admin', 'id_admin');
    }

    public function cuti()
    {
        return $this->hasMany(Cuti::class, 'id_admin', 'id_admin');
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
