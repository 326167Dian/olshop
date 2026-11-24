<?php

namespace App\Models\Concerns;

use App\Jobs\PingIndexNow;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;

/**
 * Trait Auto SEO. Model yang memakainya wajib mengisi:
 *   seoTitle(), seoDescription(), seoUrl()
 * dan boleh mengisi: seoImage(), seoSchema(), seoIndexable(),
 *   seoLastModified(), scopeSeoSitemap(), seoWatchedFields().
 *
 * Meta TIDAK disimpan ke database (dihitung saat halaman dibuka), jadi
 * transaksi kasir/stok yang menyimpan model ini tidak terbebani.
 */
trait HasSeo
{
    public static function bootHasSeo(): void
    {
        static::saved(function ($model) {
            $watched = $model->seoWatchedFields();
            $relevant = $model->wasRecentlyCreated
                || $watched === []
                || $model->wasChanged($watched);

            if (! $relevant) {
                return; // mis. hanya stok yang berubah -> abaikan
            }

            Cache::forget('seo.sitemap');
            PingIndexNow::queueUrl($model->seoUrl());
        });

        static::deleted(function ($model) {
            Cache::forget('seo.sitemap');
            PingIndexNow::queueUrl($model->seoUrl());
        });
    }

    abstract public function seoTitle(): string;

    abstract public function seoDescription(): string;

    abstract public function seoUrl(): string;

    public function seoImage(): ?string
    {
        return null;
    }

    public function seoSchema(): ?array
    {
        return null;
    }

    /** false = halaman diberi noindex dan tidak masuk sitemap */
    public function seoIndexable(): bool
    {
        return true;
    }

    public function seoLastModified(): ?\DateTimeInterface
    {
        return $this->updated_at ?? null;
    }

    /** Kolom yang bila berubah dianggap "konten berubah". [] = semua. */
    public function seoWatchedFields(): array
    {
        return [];
    }

    /** Filter data untuk sitemap. Override di model. */
    public function scopeSeoSitemap(Builder $query): Builder
    {
        return $query;
    }
}
