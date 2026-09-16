<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Absensi extends Model
{
    protected $table = 'absensi';
    protected $primaryKey = 'id_absensi';

    protected $fillable = [
        'id_admin',
        'id_jadwal',
        'id_shift',
        'tanggal',
        'jam_masuk',
        'jam_pulang',
        'status',
        'keterangan',
        'sumber',
        'dicatat_oleh',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    public function admin()
    {
        return $this->belongsTo(Admin::class, 'id_admin', 'id_admin');
    }

    public function jadwal()
    {
        return $this->belongsTo(JadwalShift::class, 'id_jadwal', 'id_jadwal');
    }

    public function shift()
    {
        return $this->belongsTo(MasterShift::class, 'id_shift', 'id_shift');
    }

    public function pencatat()
    {
        return $this->belongsTo(Admin::class, 'dicatat_oleh', 'id_admin');
    }

    public function lembur()
    {
        return $this->hasOne(Lembur::class, 'id_absensi', 'id_absensi');
    }

    /**
     * Hadir/Terlambat berdasarkan jam_masuk aktual vs jam_masuk shift +
     * toleransi_telat. Kalau tidak ada shift acuan (absen tanpa jadwal),
     * default ke 'hadir' -- tidak ada dasar untuk menilai keterlambatan.
     */
    public static function hitungStatusMasuk(?MasterShift $shift, ?string $jamMasukAktual): string
    {
        if (!$shift || !$jamMasukAktual) {
            return 'hadir';
        }

        $batas = $shift->batasTerlambat();
        $aktual = Carbon::parse($jamMasukAktual);

        return $aktual->format('H:i:s') > $batas->format('H:i:s') ? 'terlambat' : 'hadir';
    }

    /**
     * Selisih jam pulang aktual vs jam_pulang shift, dibulatkan 2 desimal.
     * 0 kalau tidak ada shift acuan atau pulang lebih awal/tepat waktu.
     */
    public static function hitungJamLembur(?MasterShift $shift, ?string $jamPulangAktual): float
    {
        if (!$shift || !$jamPulangAktual) {
            return 0;
        }

        $jadwalPulang = Carbon::parse($shift->jam_pulang);
        $aktual = Carbon::parse($jamPulangAktual);

        if ($aktual->lessThanOrEqualTo($jadwalPulang)) {
            return 0;
        }

        return round($jadwalPulang->diffInMinutes($aktual) / 60, 2);
    }
}
