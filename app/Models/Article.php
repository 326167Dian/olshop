<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use \Illuminate\Database\Eloquent\Factories\HasFactory;

class Article extends Model
{
    use \App\Models\Concerns\HasSeo;

    use HasFactory;

    protected $table = 'articles';

    protected $primaryKey = 'id';

    protected $fillable = ['user_id', 'title', 'content', 'slug', 'thumbnail', 'status'];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function admin()
    {
        return $this->belongsTo(Admin::class, 'user_id', 'id_admin');
    }

    /* ===================== Auto SEO ===================== */

    public function seoTitle(): string
    {
        return (string) $this->title;
    }

    public function seoDescription(): string
    {
        return \App\Services\Seo\SeoGenerator::description($this->content, (string) $this->title);
    }

    public function seoUrl(): string
    {
        return route('article.show', $this->slug);
    }

    public function seoImage(): ?string
    {
        return $this->thumbnail ? asset('storage/'.$this->thumbnail) : null;
    }

    public function seoIndexable(): bool
    {
        return $this->status === 'published';
    }

    public function scopeSeoSitemap($query)
    {
        return $query->where('status', 'published');
    }

    public function seoWatchedFields(): array
    {
        return ['title', 'content', 'slug', 'thumbnail', 'status'];
    }

    public function seoSchema(): ?array
    {
        return \App\Services\Seo\SchemaBuilder::article(
            headline: (string) $this->title,
            url: $this->seoUrl(),
            image: $this->seoImage(),
            publishedAt: $this->created_at,
            updatedAt: $this->updated_at,
            author: $this->admin?->nama_lengkap,
        );
    }
}
