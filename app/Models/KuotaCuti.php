<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KuotaCuti extends Model
{
    protected $table = 'kuota_cuti';
    protected $primaryKey = 'id_kuota';

    protected $fillable = [
        'id_admin',
        'tahun',
        'kuota_hari',
    ];

    public function admin()
    {
        return $this->belongsTo(Admin::class, 'id_admin', 'id_admin');
    }

    /**
     * Kuota default kalau belum ada baris kuota_cuti untuk pegawai+tahun ini.
     */
    public const KUOTA_DEFAULT = 12;

    public static function kuotaUntuk(int $idAdmin, int $tahun): int
    {
        return self::where('id_admin', $idAdmin)->where('tahun', $tahun)->value('kuota_hari')
            ?? self::KUOTA_DEFAULT;
    }

    public static function terpakaiUntuk(int $idAdmin, int $tahun): int
    {
        return (int) Cuti::where('id_admin', $idAdmin)
            ->where('jenis_cuti', 'tahunan')
            ->where('status', 'disetujui')
            ->whereYear('tanggal_mulai', $tahun)
            ->sum('jumlah_hari');
    }

    public static function sisaUntuk(int $idAdmin, int $tahun): int
    {
        return self::kuotaUntuk($idAdmin, $tahun) - self::terpakaiUntuk($idAdmin, $tahun);
    }
}
