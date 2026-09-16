<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GajiDetail extends Model
{
    protected $table = 'gaji_detail';
    protected $primaryKey = 'id_gaji_detail';
    public $timestamps = false;

    /**
     * `total` sengaja TIDAK masuk fillable -- kolom generated (STORED) di
     * database, dihitung otomatis oleh MySQL dari kolom lain (lihat migrasi
     * create_gaji_detail_table). Setelah create()/update() perlu refresh()
     * untuk membaca nilai terbarunya.
     */
    protected $fillable = [
        'id_gaji',
        'id_admin',
        'periode_bulan',
        'tgl_awal',
        'tgl_akhir',
        'jumlah_hari',
        'gaji_pokok',
        'transportasi',
        'konsumsi',
        'lembur',
        'komisi',
        'pinjaman',
        'dibuat_oleh',
        'disetujui_oleh',
    ];

    protected $casts = [
        'tgl_awal' => 'date',
        'tgl_akhir' => 'date',
    ];

    public function gaji()
    {
        return $this->belongsTo(Gaji::class, 'id_gaji', 'id_gaji');
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class, 'id_admin', 'id_admin');
    }

    public function pembuat()
    {
        return $this->belongsTo(Admin::class, 'dibuat_oleh', 'id_admin');
    }

    public function penyetuju()
    {
        return $this->belongsTo(Admin::class, 'disetujui_oleh', 'id_admin');
    }

    public function getPeriodeTextAttribute(): string
    {
        [$tahun, $bulan] = explode('-', $this->periode_bulan);

        return self::namaBulan((int) $bulan) . ' ' . $tahun;
    }

    public static function namaBulan(int $bulan): string
    {
        $nama = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];

        return $nama[$bulan] ?? '';
    }
}
