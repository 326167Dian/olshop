<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use \App\Models\Concerns\HasSeo;

    protected $table = 'barang';

    protected $primaryKey = 'id_barang';

    public $incrementing = true;

    public $timestamps = false; // Karena tidak ada field created_at dan updated_at

    protected $fillable = [
        'kd_barang',
        'category_id',
        'nm_barang',
        'stok_barang',
        'stok_buffer',
        'stok_grosir',
        'sat_barang',
        'sat_grosir',
        'konversi',
        'jenisobat',
        'hna',
        'diskon',
        'hrgsat_barang',
        'hrgsat_grosir',
        'hrgjual_barang',
        'hrgjual_barang1',
        'hrgjual_barang2',
        'komisi',
        'zataktif',
        'indikasi',
        'ket_barang',
        'petugas',
        'dosis',
        'waktu',
        't30',
        't60',
        'gr',
        'q30',
        'status',
        'display',
        'image',
        'promosi',
        'tgl',
        'updated_by'
    ];

    protected $casts = [
        'stok_barang'     => 'double',
        'stok_buffer'     => 'double',
        'hna'             => 'double',
        'diskon'          => 'double',
        'hrgsat_barang'   => 'double',
        'hrgjual_barang'  => 'double',
        'komisi'          => 'double',
        'waktu'           => 'datetime',
        't30'             => 'integer',
        't60'             => 'integer',
        'gr'              => 'integer',
        'q30'             => 'integer',
        'status'          => 'string',
        'display'         => 'string',
    ];

    /**
     * Obat berlabel 'resep' hanya boleh dilihat pelanggan yang sudah login --
     * storefront ini dipakai sebagai landing page iklan Meta, dan menampilkan
     * obat resep ke pengunjung publik berisiko akun iklan kena banned. Dipakai
     * di semua listing/detail produk sisi pelanggan (lihat HomepageController,
     * ProductController::index/search/produkKategori/detail).
     */
    public function scopeVisibleFor($query, bool $isLoggedIn)
    {
        if (!$isLoggedIn) {
            $query->where('display', 'bebas');
        }

        return $query;
    }

    public function jenisObat()
    {
        return $this->belongsTo(JenisObat::class, 'jenisobat', 'jenisobat');
        // (ModelTujuan::class, foreign_key_di_produk, primary_key_di_jenis_obat)
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id', 'id');
        // (ModelTujuan::class, foreign_key_di_produk, primary_key_di_category)
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class, 'updated_by', 'id_admin');
    }

    public function satuanBertingkat()
    {
        return $this->hasMany(SatuanBarang::class, 'kd_barang', 'kd_barang')->orderBy('urutan');
    }

    /**
     * ket_barang biasanya HTML dari CKEditor (sudah punya <p>/<br> sendiri),
     * tapi sebagian data lama cuma teks polos dengan baris baru literal yang
     * akan hilang kalau dicetak mentah ke HTML. nl2br() di sini cuma dipakai
     * saat belum ada tag block/br sama sekali, supaya tidak dobel breaknya
     * saat kontennya memang sudah HTML asli dari CKEditor.
     */
    public function getKetBarangHtmlAttribute(): string
    {
        $value = (string) $this->ket_barang;

        if ($value === '') {
            return '';
        }

        if (preg_match('/<(p|br|div|li|ul|ol)\b/i', $value)) {
            return $value;
        }

        return nl2br(e($value));
    }

    /* ===================== Auto SEO ===================== */

    public function seoTitle(): string
    {
        return (string) $this->nm_barang;
    }

    public function seoDescription(): string
    {
        $text = \App\Services\Seo\SeoGenerator::plain($this->ket_barang)
            ?: \App\Services\Seo\SeoGenerator::plain($this->indikasi);

        $fallback = 'Beli '.$this->nm_barang.' di '.\App\Services\Seo\SeoManager::siteName()
            .'. Produk asli, harga bersahabat, bisa diantar ke rumah.';

        return \App\Services\Seo\SeoGenerator::description($text, $fallback);
    }

    public function seoUrl(): string
    {
        return route('produk.detail', $this->getKey());
    }

    public function seoImage(): ?string
    {
        return $this->image ? asset('storage/'.$this->image) : null;
    }

    /** Obat resep & produk nonaktif tidak di-index (selaras dengan scopeVisibleFor). */
    public function seoIndexable(): bool
    {
        return $this->status === 'active' && $this->display === 'bebas';
    }

    public function seoLastModified(): ?\DateTimeInterface
    {
        return $this->waktu;
    }

    /** Hanya perubahan kolom ini yang memicu ping/refresh sitemap (bukan stok/kasir). */
    public function seoWatchedFields(): array
    {
        return ['nm_barang', 'ket_barang', 'indikasi', 'image', 'display', 'status', 'hrgjual_barang', 'category_id'];
    }

    public function scopeSeoSitemap($query)
    {
        return $query->where('status', 'active')->where('display', 'bebas');
    }

    public function seoSchema(): ?array
    {
        return \App\Services\Seo\SchemaBuilder::product(
            name: (string) $this->nm_barang,
            url: $this->seoUrl(),
            price: (float) $this->hrgjual_barang,
            image: $this->seoImage(),
            description: $this->seoDescription(),
            inStock: (float) $this->stok_barang > 0,
            sku: $this->kd_barang,
            category: $this->category?->name,
        );
    }
}
