<?php

use App\Services\Seo\SeoManager;

if (! function_exists('seo')) {
    /** Akses meta SEO halaman aktif: seo()->for($produk) */
    function seo(): SeoManager
    {
        return app(SeoManager::class);
    }
}
