<?php

namespace App\Services\Seo;

/**
 * Pembuat JSON-LD (schema.org) siap pakai.
 */
class SchemaBuilder
{
    public static function product(
        string $name,
        string $url,
        float $price,
        ?string $image = null,
        ?string $description = null,
        bool $inStock = true,
        ?string $sku = null,
        ?string $category = null,
    ): array {
        return array_filter([
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            'name' => $name,
            'image' => $image,
            'description' => $description ?: null,
            'sku' => $sku ?: null,
            'category' => $category ?: null,
            'offers' => array_filter([
                '@type' => 'Offer',
                'url' => $url,
                'priceCurrency' => 'IDR',
                'price' => $price > 0 ? round($price) : null,
                'availability' => $inStock
                    ? 'https://schema.org/InStock'
                    : 'https://schema.org/OutOfStock',
                'seller' => ['@type' => 'Organization', 'name' => SeoManager::siteName()],
            ], fn ($v) => $v !== null),
        ], fn ($v) => $v !== null);
    }

    public static function article(string $headline, string $url, ?string $image, $publishedAt, $updatedAt, ?string $author = null): array
    {
        return array_filter([
            '@context' => 'https://schema.org',
            '@type' => 'Article',
            'headline' => mb_substr($headline, 0, 110),
            'image' => $image,
            'mainEntityOfPage' => $url,
            'datePublished' => $publishedAt?->toAtomString(),
            'dateModified' => ($updatedAt ?? $publishedAt)?->toAtomString(),
            'author' => ['@type' => 'Person', 'name' => $author ?: SeoManager::siteName()],
            'publisher' => ['@type' => 'Organization', 'name' => SeoManager::siteName()],
        ], fn ($v) => $v !== null);
    }

    /** Data apotek dari Company Setting -> schema Pharmacy (untuk beranda). */
    public static function pharmacy(): array
    {
        $c = SeoManager::company();

        return array_filter([
            '@context' => 'https://schema.org',
            '@type' => 'Pharmacy',
            'name' => SeoManager::siteName(),
            'url' => route('home-page'),
            'logo' => $c->logo ? asset('storage/'.$c->logo) : null,
            'image' => $c->logo ? asset('storage/'.$c->logo) : null,
            'email' => $c->email ?: null,
            'telephone' => $c->telepon ?: null,
            'address' => $c->alamat ? [
                '@type' => 'PostalAddress',
                'streetAddress' => SeoGenerator::plain($c->alamat),
                'addressLocality' => config('seo.city'),
                'addressCountry' => 'ID',
            ] : null,
            'geo' => ($c->kehadiran_lat && $c->kehadiran_lng) ? [
                '@type' => 'GeoCoordinates',
                'latitude' => (float) $c->kehadiran_lat,
                'longitude' => (float) $c->kehadiran_lng,
            ] : null,
        ], fn ($v) => $v !== null);
    }

    /** $items = [['Beranda', url], ['Vitamin', url], ...] */
    public static function breadcrumb(array $items): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => collect($items)->values()->map(fn ($item, $i) => [
                '@type' => 'ListItem',
                'position' => $i + 1,
                'name' => $item[0],
                'item' => $item[1],
            ])->all(),
        ];
    }

    /** $faqs = [['Pertanyaan?', 'Jawaban.'], ...] */
    public static function faq(array $faqs): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => collect($faqs)->map(fn ($f) => [
                '@type' => 'Question',
                'name' => $f[0],
                'acceptedAnswer' => ['@type' => 'Answer', 'text' => $f[1]],
            ])->all(),
        ];
    }
}
