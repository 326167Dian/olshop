<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Schema;

class SeoMeta extends Model
{
    protected $fillable = ['meta_title', 'meta_description', 'og_image', 'noindex'];

    protected $casts = ['noindex' => 'boolean'];

    public function seoable(): MorphTo
    {
        return $this->morphTo();
    }

    /** Supaya halaman tidak error bila migrasi seo_metas belum dijalankan. */
    public static function tableReady(): bool
    {
        static $ready = null;

        return $ready ??= rescue(fn () => Schema::hasTable('seo_metas'), false, false);
    }

    public static function findFor(Model $model): ?self
    {
        if (! static::tableReady()) {
            return null;
        }

        return static::where('seoable_type', $model->getMorphClass())
            ->where('seoable_id', $model->getKey())
            ->first();
    }
}
