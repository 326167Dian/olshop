<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JadwalShift extends Model
{
    protected $table = 'jadwal_shift';
    protected $primaryKey = 'id_jadwal';

    protected $fillable = [
        'id_admin',
        'id_shift',
        'tanggal',
        'status_approval',
        'diajukan_oleh',
        'disetujui_oleh',
        'disetujui_pada',
        'catatan',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'disetujui_pada' => 'datetime',
    ];

    public function admin()
    {
        return $this->belongsTo(Admin::class, 'id_admin', 'id_admin');
    }

    public function shift()
    {
        return $this->belongsTo(MasterShift::class, 'id_shift', 'id_shift');
    }

    public function pengaju()
    {
        return $this->belongsTo(Admin::class, 'diajukan_oleh', 'id_admin');
    }

    public function penyetuju()
    {
        return $this->belongsTo(Admin::class, 'disetujui_oleh', 'id_admin');
    }

    public function absensi()
    {
        return $this->hasOne(Absensi::class, 'id_jadwal', 'id_jadwal');
    }
}
