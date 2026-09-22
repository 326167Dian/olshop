<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CompanySetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama_perusahaan',
        'email',
        'website',
        'deskripsi',
        'logo',
        'qris_image',
        'notification_sound',
        'alamat',
        'telepon',
        'peta_lokasi',
        'catatan',
        'komisi_reseller',
        'kehadiran_lat',
        'kehadiran_lng',
        'kehadiran_radius',
    ];

    /**
     * Jarak (meter) dari titik koordinat apotek ke koordinat yang diberikan,
     * pakai rumus Haversine. Null kalau lokasi apotek belum diisi -- artinya
     * validasi radius belum bisa/perlu dijalankan.
     */
    public function jarakMeterDari(float $lat, float $lng): ?float
    {
        if ($this->kehadiran_lat === null || $this->kehadiran_lng === null) {
            return null;
        }

        $earthRadius = 6371000;

        $latFrom = deg2rad((float) $this->kehadiran_lat);
        $lngFrom = deg2rad((float) $this->kehadiran_lng);
        $latTo = deg2rad($lat);
        $lngTo = deg2rad($lng);

        $latDelta = $latTo - $latFrom;
        $lngDelta = $lngTo - $lngFrom;

        $a = sin($latDelta / 2) ** 2 + cos($latFrom) * cos($latTo) * sin($lngDelta / 2) ** 2;
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    /**
     * Sama seperti jarakMeterDari(), tapi dalam kilometer -- dipakai untuk
     * penghitungan tarif & radius maksimal pengantaran (lihat DeliveryPricingService),
     * supaya tidak ada konversi meter/km manual yang gampang salah di tempat lain.
     */
    public function jarakKmDari(float $lat, float $lng): ?float
    {
        $meter = $this->jarakMeterDari($lat, $lng);

        return $meter === null ? null : $meter / 1000;
    }
}
