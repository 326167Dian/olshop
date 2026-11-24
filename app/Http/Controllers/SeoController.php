<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;

class SeoController extends Controller
{
    public function sitemap()
    {
        $minutes = (int) config('seo.sitemap.cache_minutes', 60);

        $xml = Cache::remember('seo.sitemap', now()->addMinutes($minutes), function () {
            $urls = [];

            foreach (config('seo.sitemap.static_routes', []) as $name => [$freq, $priority]) {
                if (Route::has($name)) {
                    $urls[] = ['loc' => route($name), 'lastmod' => null, 'freq' => $freq, 'priority' => $priority];
                }
            }

            foreach (config('seo.sitemap.models', []) as $class => [$freq, $priority]) {
                $class::query()->seoSitemap()->lazyById(500)->each(function ($row) use (&$urls, $freq, $priority) {
                    if (! $row->seoIndexable()) {
                        return;
                    }
                    $urls[] = [
                        'loc' => $row->seoUrl(),
                        'lastmod' => $row->seoLastModified()?->format('Y-m-d'),
                        'freq' => $freq,
                        'priority' => $priority,
                    ];
                });
            }

            return view('seo.sitemap', ['urls' => $urls])->render();
        });

        return response($xml, 200)->header('Content-Type', 'application/xml; charset=UTF-8');
    }

    public function robots()
    {
        $host = parse_url(config('app.url'), PHP_URL_HOST);
        $isLocal = app()->isLocal() || in_array($host, ['localhost', '127.0.0.1'], true);

        if ($isLocal) {
            // Situs lokal/staging jangan sampai ter-index
            $lines = ['User-agent: *', 'Disallow: /'];
        } else {
            $lines = ['User-agent: *'];
            foreach (config('seo.robots_disallow', []) as $path) {
                $lines[] = 'Disallow: '.$path;
            }
            $lines[] = '';
            $lines[] = 'Sitemap: '.route('seo.sitemap');
        }

        return response(implode("\n", $lines)."\n", 200)->header('Content-Type', 'text/plain; charset=UTF-8');
    }

    public function indexNowKey(string $key)
    {
        abort_unless(filled(config('seo.indexnow.key')) && hash_equals((string) config('seo.indexnow.key'), $key), 404);

        return response($key, 200)->header('Content-Type', 'text/plain');
    }
}
