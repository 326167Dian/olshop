<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Category;
use App\Models\Product;
use App\Models\SeoMeta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * Setting > SEO -- kelola override manual per halaman (meta_title/meta_description/
 * og_image/noindex, tabel `seo_metas`) untuk model yang pakai trait HasSeo. Tanpa
 * override di sini, nilai otomatis dari model (seoTitle()/seoDescription()/dst, lihat
 * SeoManager::for()) yang dipakai -- lihat catatan di HasSeo.
 */
class SeoMetaController extends Controller
{
    /** slug URL => [kelas model, label, kolom nama untuk pencarian/tampilan]. */
    private const TYPES = [
        'produk' => [Product::class, 'Produk', 'nm_barang'],
        'kategori' => [Category::class, 'Kategori', 'name'],
        'artikel' => [Article::class, 'Artikel', 'title'],
    ];

    public function index(Request $request)
    {
        $type = $request->query('type', 'produk');
        abort_unless(isset(self::TYPES[$type]), 404);
        [$class, $label, $namaKolom] = self::TYPES[$type];

        $keyword = trim((string) $request->query('q', ''));

        $rows = $class::query()
            ->when($keyword !== '', fn ($q) => $q->where($namaKolom, 'like', "%{$keyword}%"))
            ->orderBy($namaKolom)
            ->paginate(20)
            ->withQueryString();

        $morphClass = (new $class())->getMorphClass();
        $overrides = SeoMeta::where('seoable_type', $morphClass)
            ->whereIn('seoable_id', $rows->pluck($rows->first()?->getKeyName() ?? 'id'))
            ->get()
            ->keyBy('seoable_id');

        return view('backend.setting.seo-meta.index', [
            'type' => $type,
            'label' => $label,
            'rows' => $rows,
            'overrides' => $overrides,
            'keyword' => $keyword,
            'types' => self::TYPES,
        ]);
    }

    public function edit(string $type, $id)
    {
        $model = $this->resolveModel($type, $id);

        $override = SeoMeta::where('seoable_type', $model->getMorphClass())
            ->where('seoable_id', $model->getKey())
            ->first();

        return view('backend.setting.seo-meta.edit', [
            'type' => $type,
            'label' => self::TYPES[$type][1],
            'model' => $model,
            'override' => $override,
            'autoTitle' => $model->seoTitle(),
            'autoDescription' => $model->seoDescription(),
            'autoImage' => $model->seoImage(),
            'autoUrl' => $model->seoUrl(),
        ]);
    }

    public function update(Request $request, string $type, $id)
    {
        $model = $this->resolveModel($type, $id);

        $validated = $request->validate([
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'og_image' => 'nullable|image|max:3048',
            'noindex' => 'nullable|boolean',
        ]);

        $existing = SeoMeta::where('seoable_type', $model->getMorphClass())
            ->where('seoable_id', $model->getKey())
            ->first();

        $data = [
            'meta_title' => $validated['meta_title'] ?: null,
            'meta_description' => $validated['meta_description'] ?: null,
            'noindex' => $request->boolean('noindex'),
        ];

        if ($request->hasFile('og_image')) {
            if ($existing?->og_image) {
                Storage::disk('public')->delete($existing->og_image);
            }
            $data['og_image'] = $request->file('og_image')->store('seo', 'public');
        }

        SeoMeta::updateOrCreate(
            ['seoable_type' => $model->getMorphClass(), 'seoable_id' => $model->getKey()],
            $data
        );

        return redirect()->route('seo-meta.index', ['type' => $type])->with('success', 'Pengaturan SEO berhasil disimpan.');
    }

    public function destroy(string $type, $id)
    {
        $model = $this->resolveModel($type, $id);

        $override = SeoMeta::where('seoable_type', $model->getMorphClass())
            ->where('seoable_id', $model->getKey())
            ->first();

        if ($override) {
            if ($override->og_image) {
                Storage::disk('public')->delete($override->og_image);
            }
            $override->delete();
        }

        return redirect()->route('seo-meta.index', ['type' => $type])->with('success', 'Override SEO dihapus, kembali memakai nilai otomatis.');
    }

    private function resolveModel(string $type, $id)
    {
        abort_unless(isset(self::TYPES[$type]), 404);
        [$class] = self::TYPES[$type];

        return $class::findOrFail($id);
    }
}
