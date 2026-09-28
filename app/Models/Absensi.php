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
        'lat_masuk',
        'lng_masuk',
        'jarak_masuk_meter',
        'lat_pulang',
        'lng_pulang',
        'jarak_pulang_meter',
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
     * Durasi kerja PENUH dari jam_masuk ke jam_pulang -- dipakai untuk shift
     * tambahan (ke-2+) di hari yang sama, yang seluruh durasinya dianggap lembur
     * (jam_lembur cuma buat informasi, bukan dasar hitung uang -- lihat
     * InventoryKehadiranController::checkout() & hitungLemburOtomatis()).
     */
    public static function hitungDurasiJam(string $jamMasuk, string $jamPulang): float
    {
        $masuk = Carbon::parse($jamMasuk);
        $pulang = Carbon::parse($jamPulang);

        return round($masuk->diffInMinutes($pulang) / 60, 2);
    }

    /**
     * Menit terlambat dihitung dari batasTerlambat() (jam_masuk shift + toleransi_telat),
     * BUKAN dari jadwal mentah -- dipakai untuk potongan gaji berjenjang (lihat
     * InventoryGajiDetailController::hitungPotonganTelatOtomatis()). 0 kalau tidak
     * telat atau tidak ada shift acuan.
     */
    public static function hitungMenitTerlambat(?MasterShift $shift, ?string $jamMasukAktual): float
    {
        if (!$shift || !$jamMasukAktual) {
            return 0;
        }

        $batas = $shift->batasTerlambat();
        $aktual = Carbon::parse($jamMasukAktual);

        if ($aktual->lessThanOrEqualTo($batas)) {
            return 0;
        }

        return $batas->diffInMinutes($aktual);
    }
}
