<?php

namespace App\Services;

use App\Models\CompanySetting;
use App\Models\LokasiAntar;
use Carbon\Carbon;

class DeliveryPricingService
{
    public const MAX_RADIUS_KM = 5.0;
    public const EXPRESS_MULTIPLIER = 2;
    public const REGULER_CUTOFF = '15:00';
    public const REGULER_DELIVERY_HOUR = 16;

    public function __construct(private CompanySetting $companySetting)
    {
    }

    /**
     * Hitung jarak + tarif pengantaran ke satu titik koordinat.
     *
     * @return array{ok: bool, km: float|null, tier: LokasiAntar|null,
     *     biaya_reguler: float|null, biaya_express: float|null, biaya: float|null,
     *     error: string|null}
     */
    public function quote(float $lat, float $lng, string $metodeAntar): array
    {
        $km = $this->companySetting->jarakKmDari($lat, $lng);

        if ($km === null) {
            return $this->rejected('Lokasi apotek belum diatur. Hubungi admin.');
        }

        if ($km > self::MAX_RADIUS_KM) {
            return $this->rejected(sprintf(
                'Alamat berada di luar radius pengantaran maksimal %g km (jarak %.2f km). Silakan pilih alamat lain yang lebih dekat atau Ambil di Toko.',
                self::MAX_RADIUS_KM,
                $km
            ));
        }

        $tier = LokasiAntar::tierUntukJarak($km);

        if (!$tier) {
            return $this->rejected(sprintf('Belum ada tarif pengantaran untuk jarak %.2f km. Hubungi admin.', $km));
        }

        $reguler = (float) $tier->biaya_antar;
        $express = $reguler * self::EXPRESS_MULTIPLIER;

        return [
            'ok' => true,
            'km' => round($km, 2),
            'tier' => $tier,
            'biaya_reguler' => $reguler,
            'biaya_express' => $express,
            'biaya' => $metodeAntar === 'express' ? $express : $reguler,
            'error' => null,
        ];
    }

    /**
     * Reguler cuma tersedia setiap hari kecuali Minggu, dan cuma untuk order yang
     * masuk sebelum jam 15:00 (biar sempat diantar hari itu juga jam 16:00).
     */
    public function regulerTersediaHariIni(?Carbon $now = null): bool
    {
        $now = $now ?: Carbon::now();

        return !$now->isSunday() && $now->format('H:i') < self::REGULER_CUTOFF;
    }

    public function estimasiAntarReguler(?Carbon $now = null): Carbon
    {
        return ($now ?: Carbon::now())->copy()->setTime(self::REGULER_DELIVERY_HOUR, 0, 0);
    }

    public function estimasiAntarExpress(?Carbon $now = null): Carbon
    {
        return ($now ?: Carbon::now())->copy()->addHours(2);
    }

    private function rejected(string $error): array
    {
        return [
            'ok' => false,
            'km' => null,
            'tier' => null,
            'biaya_reguler' => null,
            'biaya_express' => null,
            'biaya' => null,
            'error' => $error,
        ];
    }
}
