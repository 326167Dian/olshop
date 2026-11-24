<?php

namespace App\Services\Seo;

use Illuminate\Support\Str;

class SeoGenerator
{
    /** Ubah HTML (CKEditor) jadi teks polos satu baris. */
    public static function plain(?string $html): string
    {
        $html = preg_replace('/<(br|\/p|\/li|\/div)[^>]*>/i', ' ', (string) $html);
        $text = html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return trim(preg_replace('/\s+/u', ' ', $text));
    }

    /** "Judul | Nama Apotek", total <= title_max bila memungkinkan. */
    public static function title(string $text, ?string $siteName = null): string
    {
        $text = trim(static::plain($text));
        $site = $siteName ?? SeoManager::siteName();
        $sep = config('seo.separator', ' | ');
        $max = (int) config('seo.title_max', 60);

        if ($site === '' || Str::contains(Str::lower($text), Str::lower($site))) {
            return Str::limit($text, $max, '');
        }

        $room = $max - mb_strlen($sep.$site);

        if ($room < 25) {
            return Str::limit($text, $max, '');
        }

        return Str::limit($text, $room, '').$sep.$site;
    }

    public static function description(?string $html, string $fallback = ''): string
    {
        $text = static::plain($html);

        if ($text === '') {
            $text = $fallback;
        }

        $max = (int) config('seo.description_max', 160);

        return Str::limit($text, $max - 3);
    }
}
