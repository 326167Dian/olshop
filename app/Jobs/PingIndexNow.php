<?php

namespace App\Jobs;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Memberi tahu Bing/Yandex dkk (IndexNow) bahwa URL baru/berubah.
 *
 * URL dikumpulkan selama satu request lalu dikirim SEKALI setelah
 * response terkirim ke pengunjung -- tidak butuh queue worker dan
 * tidak memperlambat halaman admin.
 */
class PingIndexNow
{
    protected static array $pending = [];

    protected static bool $registered = false;

    public function __construct(public array $urls) {}

    public static function queueUrl(string $url): void
    {
        if (! static::enabled()) {
            return;
        }

        static::$pending[$url] = true;

        if (! static::$registered) {
            static::$registered = true;
            app()->terminating(function () {
                $urls = array_keys(static::$pending);
                static::$pending = [];
                static::$registered = false;

                foreach (array_chunk($urls, 1000) as $chunk) {
                    (new static($chunk))->handle();
                }
            });
        }
    }

    public static function enabled(): bool
    {
        return (bool) config('seo.indexnow.enabled')
            && filled(config('seo.indexnow.key'))
            && ! app()->runningUnitTests();
    }

    public function handle(): void
    {
        if (! static::enabled() || $this->urls === []) {
            return;
        }

        try {
            Http::timeout(8)->post(config('seo.indexnow.endpoint'), [
                'host' => parse_url(config('app.url'), PHP_URL_HOST),
                'key' => config('seo.indexnow.key'),
                'keyLocation' => url(config('seo.indexnow.key').'.txt'),
                'urlList' => array_values(array_unique($this->urls)),
            ]);
        } catch (\Throwable $e) {
            Log::warning('IndexNow gagal: '.$e->getMessage());
        }
    }
}
