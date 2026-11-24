<?php

namespace App\Services\Seo;

use App\Models\CompanySetting;
use App\Models\SeoMeta;
use Illuminate\Database\Eloquent\Model;

/**
 * Penampung meta untuk halaman yang sedang dibuka.
 *
 *   Controller:  seo()->for($produk);
 *                seo()->title('Semua Produk')->description('...');
 *                seo()->noindex();
 *   Layout:      <x-seo />
 */
class SeoManager
{
    protected ?string $title = null;
    protected ?string $description = null;
    protected ?string $canonical = null;
    protected ?string $image = null;
    protected bool $noindex = false;
    protected string $ogType = 'website';
    protected array $schemas = [];

    public static function company(): CompanySetting
    {
        static $company = null;

        return $company ??= rescue(fn () => CompanySetting::first(), null, false) ?? new CompanySetting();
    }

    public static function siteName(): string
    {
        return trim((string) (static::company()->nama_perusahaan ?: config('app.name', 'Apotek')));
    }

    public function for(Model $model): static
    {
        $meta = SeoMeta::findFor($model);

        $this->title = $meta?->meta_title ?: SeoGenerator::title($model->seoTitle());
        $this->description = $meta?->meta_description ?: $model->seoDescription();
        $this->canonical = $model->seoUrl();
        $this->image = $meta?->og_image ?: $model->seoImage();
        $this->noindex = ($meta?->noindex ?? false) || ! $model->seoIndexable();

        if ($schema = $model->seoSchema()) {
            $this->schemas[] = $schema;
            $type = $schema['@type'] ?? '';
            $this->ogType = $type === 'Product' ? 'product' : ($type === 'Article' ? 'article' : 'website');
        }

        return $this;
    }

    public function title(string $title): static
    {
        $this->title = SeoGenerator::title($title);

        return $this;
    }

    public function description(string $text): static
    {
        $this->description = SeoGenerator::description($text);

        return $this;
    }

    public function canonical(string $url): static
    {
        $this->canonical = $url;

        return $this;
    }

    public function image(?string $url): static
    {
        $this->image = $url;

        return $this;
    }

    public function noindex(bool $value = true): static
    {
        $this->noindex = $value;

        return $this;
    }

    public function addSchema(array $schema): static
    {
        $this->schemas[] = $schema;

        return $this;
    }

    public function data(): array
    {
        $company = static::company();
        $site = static::siteName();

        $image = $this->image
            ?: ($company->logo ? asset('storage/'.$company->logo) : null);

        // Canonical tanpa query string, kecuali nomor halaman (?page=2)
        $canonical = $this->canonical ?: url()->current();
        if (! $this->canonical && ($page = (int) request('page')) > 1) {
            $canonical .= '?page='.$page;
        }

        return [
            'title' => $this->title ?: $site.config('seo.separator').config('seo.tagline'),
            'description' => $this->description
                ?: SeoGenerator::description($company->deskripsi, $site.' - '.config('seo.tagline')),
            'canonical' => $canonical,
            'image' => $image,
            'noindex' => $this->noindex,
            'og_type' => $this->ogType,
            'site_name' => $site,
            'schemas' => $this->schemas,
        ];
    }
}
