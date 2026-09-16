<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cuti extends Model
{
    protected $table = 'cuti';
    protected $primaryKey = 'id_cuti';

    protected $fillable = [
        'id_admin',
        'jenis_cuti',
        'tanggal_mulai',
        'tanggal_selesai',
        'jumlah_hari',
        'alasan',
        'status',
        'disetujui_oleh',
        'disetujui_pada',
        'catatan_approval',
    ];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
        'disetujui_pada' => 'datetime',
    ];

    public function admin()
    {
        return $this->belongsTo(Admin::class, 'id_admin', 'id_admin');
    }

    public function penyetuju()
    {
        return $this->belongsTo(Admin::class, 'disetujui_oleh', 'id_admin');
    }

    /**
     * true kalau tanggal ini tercakup dalam cuti yang SUDAH disetujui --
     * dipakai rekap Absensi supaya tanggal cuti tidak dihitung sebagai Alpha.
     */
    public static function adaCutiDisetujui(int $idAdmin, string $tanggal): bool
    {
        return self::where('id_admin', $idAdmin)
            ->where('status', 'disetujui')
            ->whereDate('tanggal_mulai', '<=', $tanggal)
            ->whereDate('tanggal_selesai', '>=', $tanggal)
            ->exists();
    }
}
