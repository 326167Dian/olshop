<?php

namespace App\Support;

/**
 * Antrian event Meta Pixel yang dikirim lewat session flash, supaya event yang
 * terjadi di controller (AddToCart, Purchase, dst.) tetap terkirim walaupun
 * request-nya diakhiri redirect. Dirender oleh partial frontend.partials.meta-pixel.
 *
 * Catatan privasi: jangan kirim nama obat / nama penyakit ke Meta -- cukup ID
 * produk, jumlah, dan nilai belanja.
 */
class MetaPixel
{
    public const SESSION_KEY = 'meta_pixel_events';

    private static ?string $idCache = null;
    private static bool $idLoaded = false;

    /**
     * Pixel ID aktif: dari Setting > Input Pixel (tabel company_settings).
     * Fallback ke META_PIXEL_ID di .env bila belum diisi lewat menu.
     */
    public static function id(): ?string
    {
        if (self::$idLoaded) {
            return self::$idCache;
        }
        self::$idLoaded = true;

        $id = null;
        try {
            $setting = \App\Models\CompanySetting::first();
            if ($setting && $setting->meta_pixel_id) {
                // Diisi lewat menu: hormati saklar aktif/nonaktif
                $id = $setting->meta_pixel_aktif === false ? null : $setting->meta_pixel_id;
                return self::$idCache = $id;
            }
        } catch (\Throwable $e) {
            // Tabel/kolom belum ada (migrasi belum dijalankan) -- pakai .env saja
        }

        return self::$idCache = (config('services.meta_pixel.id') ?: null);
    }

    public static function enabled(): bool
    {
        return (bool) self::id();
    }

    public static function flash(string $event, array $params = [], ?string $eventId = null): void
    {
        if (!self::enabled()) {
            return;
        }

        $events = session()->get(self::SESSION_KEY, []);
        $events[] = array_filter([
            'event' => $event,
            'params' => $params,
            'eventID' => $eventId,
        ], fn ($v) => $v !== null);

        session()->flash(self::SESSION_KEY, $events);
    }

    /** Ambil (dan kosongkan) event yang menunggu dirender. */
    public static function pull(): array
    {
        return session()->pull(self::SESSION_KEY, []);
    }
}
