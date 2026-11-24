<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use \Illuminate\Database\Eloquent\Factories\HasFactory;


class Category extends Model
{
    use \App\Models\Concerns\HasSeo;

    use HasFactory;

    protected $table = 'categories';
    protected $primaryKey = 'id';

    protected $fillable = [
        'name',
    ];
    public function products()
    {
        return $this->hasMany(Product::class, 'category_id', 'id');
    }

    /* ===================== Auto SEO ===================== */

    public function seoTitle(): string
    {
        return 'Produk '.$this->name;
    }

    public function seoDescription(): string
    {
        return \App\Services\Seo\SeoGenerator::description(
            'Belanja produk '.$this->name.' di '.\App\Services\Seo\SeoManager::siteName()
            .'. Pilihan lengkap, harga bersahabat, bisa diantar ke rumah.'
        );
    }

    public function seoUrl(): string
    {
        return route('produk.kategori', $this->getKey());
    }

    /** Hanya kategori yang punya produk tampil-publik yang masuk sitemap. */
    public function scopeSeoSitemap($query)
    {
        return $query->whereHas('products', fn ($q) => $q->where('status', 'active')->where('display', 'bebas'));
    }

    public function seoWatchedFields(): array
    {
        return ['name'];
    }
}
