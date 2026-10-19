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

    public static function enabled(): bool
    {
        return (bool) config('services.meta_pixel.id');
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
