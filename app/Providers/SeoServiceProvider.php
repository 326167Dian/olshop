<?php

namespace App\Providers;

use App\Services\Seo\SeoManager;
use Illuminate\Support\ServiceProvider;

class SeoServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Helper seo() -- dimuat di sini supaya tidak perlu ubah composer.json
        require_once app_path('helpers.php');

        // Satu instance per request
        $this->app->scoped(SeoManager::class);
    }
}
