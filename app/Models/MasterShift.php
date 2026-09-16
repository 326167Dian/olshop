<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterShift extends Model
{
    protected $table = 'master_shift';
    protected $primaryKey = 'id_shift';

    protected $fillable = [
        'nama_shift',
        'jam_masuk',
        'jam_pulang',
        'toleransi_telat',
        'status_aktif',
    ];

    protected $casts = [
        'status_aktif' => 'boolean',
    ];

    public function jadwal()
    {
        return $this->hasMany(JadwalShift::class, 'id_shift', 'id_shift');
    }

    /**
     * Batas waktu masuk sebelum dianggap Terlambat (jam_masuk + toleransi_telat).
     */
    public function batasTerlambat(): \Carbon\Carbon
    {
        return \Carbon\Carbon::parse($this->jam_masuk)->addMinutes($this->toleransi_telat);
    }
}
